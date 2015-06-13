<?php namespace Nqxcode\LuceneSearch\Model;

use Illuminate\Database\Eloquent\Model;
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
                'private_key' => array_get($options, 'private_key', 'id')
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
    private function createModelByClassUid($classUid)
    {
        foreach ($this->configuration as $config) {
            if ($config['class_uid'] == $classUid) {
                return $config['repository'];
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
    public function privateKeyPair(Model $model)
    {
        $c = $this->config($model);
        return ['private_key', $model->{$c['private_key']}];
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
        $c = $this->config($model);
        return $c['fields'];
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
        $repository = $this->createModelByClassUid(object_get($hit, 'class_uid'));
        $model = $repository->find(object_get($hit, 'private_key'));

        return $model;
    }

    /**
     * Get all models by query hits.
     *
     * @param QueryHit[] $hits
     * @param array $options - limit  : max number of records to return
     *                       - offset : number of records to skip
     * @return array - 0 : array with models
     *                 1 : total count
     */
    public function models($hits, array $options = [])
    {
        // Get models from hits.
        $results = array_map(
            function ($hit) {
                return $this->model($hit);
            },
            $hits
        );

        // Skip empty or not searchable.
        $results = array_filter(
            $results,
            function ($model) {
                if (!is_null($model)) {
                    if (method_exists($model, 'isSearchable')) {
                        return $model->{'isSearchable'}();
                    } else {
                        return true;
                    }
                }
                return false;
            }
        );

        $results = array_values($results);
        $totalCount = count($results);

        // Limit results.
        if (isset($options['limit']) && isset($options['offset'])) {
            $results = array_slice($results, $options['offset'], $options['limit']);
        }

        return [$results, $totalCount];
    }
}
