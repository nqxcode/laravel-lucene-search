<?php namespace Nqxcode\LuceneSearch\Model;

use Illuminate\Database\Eloquent\Model;
use Nqxcode\LuceneSearch\Support\Collection;
use ZendSearch\Lucene\Search\QueryHit;

/**
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
     * Get list of models instances.
     *
     * @return Model[]
     */
    public function modelRepositories()
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
     * Get fields for indexing for model.
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
        $c = $this->config($model);

        $attributes = [];

        $default = 'optional_attributes';
        $field = array_get($c, $default) === true ? $default : array_get($c, "{$default}.field");

        if (!is_null($field)) {
            $attributes = object_get($model, $field, []);

            if (array_values($attributes) === $attributes) {

                // Transform to the associative
                $attributes = array_combine(
                    array_map(
                        function ($i) use ($field) {
                            return "{$field}_{$i}";
                        },
                        array_keys($attributes)
                    ),
                    $attributes
                );
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
     * Get the model by query hit.
     *
     * @param QueryHit $hit
     * @return \Illuminate\Database\Eloquent\Collection|Model|static
     */
    public function model(QueryHit $hit)
    {
        $model = $this->newInstanceBy(object_get($hit, 'class_uid'));

        // Set private key value
        $model->setAttribute($model->getKeyName(), object_get($hit, 'primary_key'));

        // Set score
        // $model->setAttribute('score', $hit->score);

        return $model;
    }

    /**
     * Get models by query hits.
     *
     * @param QueryHit[] $hits
     * @param bool $lazy
     * @return Collection
     */
    public function models($hits, $lazy = false)
    {
        list($collection, $searchableIdsGroups) = $this->parse($hits);
        $searchable = $this->actualize($collection, $searchableIdsGroups);

        return $lazy ? $searchable : $searchable->unlazy();
    }

    /**
     * Parse found hits.
     *
     * @param QueryHit[] $hits
     * @return array
     */
    private function parse($hits)
    {
        $collection = Collection::make([]);

        $modelsGroups = [];
        foreach ($hits as $hit) {
            $model = $this->model($hit);
            $class = get_class($model);

            // Grouping models by class
            $modelsGroups[$class][] = $model;
        }

        $searchableIdsGroups = [];
        foreach ($modelsGroups as $class => $models) {
            /** @var Model $modelInstance */
            $modelInstance = new $class;

            if (method_exists($modelInstance, 'searchableIds')) {
                $searchableIds = $modelInstance->{'searchableIds'}();
            } else {
                $searchableIds = $modelInstance->query()->lists($modelInstance->getKeyName());
            }

            // Set searchable id list for model's class
            $searchableIdsGroups[$class] = $searchableIds ?: [null];

            foreach ($models as $model) {
                $collection[] = $model;
            }
        }
        return [$collection, $searchableIdsGroups];
    }

    /**
     * Get collection with only searchable models.
     *
     * @param Collection $collection
     * @param array $searchableIdsGroups
     * @return Collection
     */
    private function actualize(Collection $collection, array $searchableIdsGroups)
    {
        $searchable = [];

        foreach ($collection as $model) {
            $primaryValue = $model->{$model->getKeyName()};
            $searchableIds = array_get($searchableIdsGroups, get_class($model), []);

            if (in_array($primaryValue, $searchableIds)) {
                $searchable[] = $model;
            }
        }

        return Collection::make($searchable);
    }
}
