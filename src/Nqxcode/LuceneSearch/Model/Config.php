<?php namespace Nqxcode\LuceneSearch\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Nqxcode\LuceneSearch\Support\Collection;
use ZendSearch\Lucene\Search\QueryHit;

/**
 * TODO add unit tests
 * Class Config
 * @package Nqxcode\LuceneSearch
 */
class Config
{
    /**
     * The list of configurations for each searchable model.
     *
     * @var array
     */
    private $configuration = [];

    /**
     * Model factory.
     *
     * @var \Nqxcode\LuceneSearch\Model\Factory
     */
    private $modelFactory;

    /**
     * Create configuration for models.
     *
     * @param array $configuration
     * @param Factory $modelFactory
     * @throws \InvalidArgumentException
     */
    public function __construct(array $configuration, Factory $modelFactory)
    {
        if (empty($configuration)) {
            throw new \InvalidArgumentException('Configurations of models are empty.');
        }

        $this->modelFactory = $modelFactory;

        foreach ($configuration as $className => $options) {

            $fields = array_get($options, 'fields', []);
            $optionalAttributes = array_get($options, 'optional_attributes', []);
            $boost = array_get($options, 'boost', true);

            if (count($fields) == 0 && count($optionalAttributes) == 0) {
                throw new \InvalidArgumentException(
                    "Parameter 'fields' and/or 'optional_attributes ' for '{$className}' class must be specified."
                );
            }

            $modelRepository = $modelFactory->newInstance($className);
            $classUid = $modelFactory->classUid($className);

            $this->configuration[] = [
                'repository' => $modelRepository,
                'class_uid' => $classUid,
                'fields' => $fields,
                'optional_attributes' => $optionalAttributes,
                'boost' => $boost,
                'primary_key' => array_get($options, 'primary_key', 'id')
            ];
        }
    }

    /**
     * Get configuration for model.
     *
     * @param Model $model
     * @return array
     * @throws \InvalidArgumentException
     */
    private function config(Model $model)
    {
        $classUid = $this->modelFactory->classUid($model);

        foreach ($this->configuration as $config) {
            if ($config['class_uid'] === $classUid) {
                return $config;
            }
        }

        throw new \InvalidArgumentException(
            "Configuration doesn't exist for model of class '" . get_class($model) . "'."
        );
    }

    /**
     * Create instance of model by class UID.
     *
     * @param $classUid
     * @return Model
     * @throws \InvalidArgumentException
     */
    private function newInstanceBy($classUid)
    {
        foreach ($this->configuration as $config) {
            if ($config['class_uid'] == $classUid) {
                /** @var Model $repository */
                $repository = $config['repository'];

                return $repository->newInstance();
            }
        }

        throw new \InvalidArgumentException("Can't find class for classUid: '{$classUid}'.");
    }

    /**
     * Get the model by query hit.
     *
     * @param QueryHit $hit
     * @return \Illuminate\Database\Eloquent\Collection|Model|static
     */
    private function model(QueryHit $hit)
    {
        $model = $this->newInstanceBy($hit->{'class_uid'});

        // Set primary key value
        $model->setAttribute($model->getKeyName(), $hit->{'primary_key'});

        // Set score
        // $model->setAttribute('score', $hit->score);

        return $model;
    }

    /**
     * Get classes uid list for hits.
     *
     * @param QueryHit[] $hits
     * @return array
     */
    private function classUidList($hits)
    {
        return array_unique(array_map(function ($hit) {
            return $hit->{'class_uid'};
        }, $hits));
    }

    /**
     * Get searchable id list grouped by classes.
     *
     * @param QueryHit[] $hits
     * @return array
     */
    private function groupedSearchableIdsAsKeys(array $hits)
    {
        $groupedIdsAsKeys = [];

        foreach ($this->classUidList($hits) as $classUid) {
            /** @var Model|Builder $model */
            $model = $this->newInstanceBy($classUid);
            $primaryKey = $model->getKeyName();

            if (!method_exists($model, 'searchableIds')) { // If not exists get full id list
                $searchableIds = $model->newQuery()->lists($primaryKey);
            } else {
                $searchableIds = $model->{'searchableIds'}();
            }

            $searchableIds = \Illuminate\Support\Collection::make($searchableIds)->all();

            // Set searchable id list for model's class
            $groupedIdsAsKeys[get_class($model)] = $searchableIds ? array_flip($searchableIds): [];
        }

        return $groupedIdsAsKeys;
    }

    /**
     * Get full list of models instances.
     *
     * @return Model[]|Builder[]
     */
    public function repositories()
    {
        $repositories = [];
        foreach ($this->configuration as $config) {
            $repositories[] = $config['repository'];
        }
        return $repositories;
    }

    /**
     * Get 'key-value' pair for private key of model.
     *
     * @param Model $model
     * @return array
     */
    public function primaryKeyPair(Model $model)
    {
        $c = $this->config($model);
        return ['primary_key', $model->{$c['primary_key']}];
    }

    /**
     * Get 'key-value' pair for UID of model class.
     *
     * @param Model $model
     * @return array
     */
    public function classUidPair(Model $model)
    {
        $c = $this->config($model);
        return ['class_uid', $c['class_uid']];
    }

    /**
     * Get model fields for indexing.
     *
     * @param Model $model
     * @return array
     */
    public function fields(Model $model)
    {
        $fields = [];
        $c = $this->config($model);

        foreach ($c['fields'] as $key => $value) {
            $boost = 1;
            $field = $value;

            if (is_array($value)) {
                $boost = array_get($value, 'boost', 1);
                $field = $key;
            }

            $fields[$field] = ['boost' => $boost];
        }

        return $fields;
    }

    /**
     * Get optional attributes for indexing for model.
     *
     * @param Model $model
     * @return array
     */
    public function optionalAttributes(Model $model)
    {
        $attributes = [];

        $c = $this->config($model);
        $option = snake_case(__FUNCTION__);

        if (!is_null(array_get($c, $option))) {
            $accessor = array_get($c, $option) === true ? $option : array_get($c, "{$option}.accessor");

            if (!is_null($accessor)) {
                $attributes = $model->{$accessor} ?: [];

                if (array_values($attributes) === $attributes) {

                    // Transform to the associative
                    $attributes = array_combine(
                        array_map(
                            function ($i) use ($accessor) {
                                return "{$accessor}_{$i}";
                            },
                            array_keys($attributes)
                        ),
                        $attributes
                    );
                }
            }
        }

        $attributes = array_map(function ($value) {
            $boost = 1;

            if (is_array($value)) {
                $boost = array_get($value, 'boost', 1);
                $value = array_get($value, 'value');
            }

            return ['boost' => $boost, 'value' => $value];
        }, $attributes);

        return $attributes;
    }

    /**
     * Get boost for model.
     *
     * @param Model $model
     * @return int
     */
    public function boost(Model $model)
    {
        $boost = 1;

        $c = $this->config($model);
        $option = snake_case(__FUNCTION__);

        if (!is_null(array_get($c, $option))) {
            $accessor = array_get($c, $option) === true ? $option : array_get($c, "{$option}.accessor");
            if (!is_null($accessor)) {
                $boost = $model->{$accessor} ?: 1;
            }
        }

        return $boost;
    }

    /**
     * Get models by query hits.
     *
     * @param QueryHit[] $hits
     * @return Collection
     */
    public function models($hits)
    {
        $models = [];
        $groupedIdsAsKeys = $this->groupedSearchableIdsAsKeys($hits);

        foreach ($hits as $hit) {
            $model = $this->model($hit);

            $id = $model->{$model->getKeyName()};
            $searchableIds = array_get($groupedIdsAsKeys, get_class($model), []);

            if (isset($searchableIds[$id])) {
                $models[] = $model;
            }
        }

        return Collection::make($models);
    }
}
