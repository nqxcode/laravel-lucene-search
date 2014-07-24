<?php namespace Nqxcode\LaravelSearch;

use Illuminate\Database\Eloquent\Model;
use ZendSearch\Lucene\Search\QueryHit;

/**
 * Class Config
 * @package Nqxcode\LaravelSearch
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
     * Create configuration for models.
     *
     * @param $configuration
     * @param $repositoryCreator
     * @throws \InvalidArgumentException
     */
    public function __construct(array $configuration, RepoFactory $repositoryCreator)
    {
        $this->repositoryCreator = $repositoryCreator;

        foreach ($configuration as $className => $options) {

            $fields = array_get($options, 'fields', []);

            if (count($fields) == 0) {
                throw new \InvalidArgumentException(
                    "Parameter 'fields' for the class '{$className}' should be specified."
                );
            }

            $repo = $repositoryCreator->create($className);

            $this->configuration[] = [
                'repository' => $repo,
                'class_uid' => $repositoryCreator->classUid($repo),
                'fields' => $fields,
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
        $hash = $this->repositoryCreator->classUid($model);

        foreach ($this->configuration as $config) {
            if ($config['class_uid'] === $hash) {
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
    public function repositories()
    {
        $repositories = [];
        foreach ($this->configuration as $config) {
            $repositories[] = $config['repository'];
        }
        return $repositories;
    }

    /**
     * Get 'key-value' pair for private key for model.
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
     * Get the model by query hit.
     *
     * @param QueryHit $hit
     * @return \Illuminate\Database\Eloquent\Collection|Model|static
     */
    public function model(QueryHit $hit)
    {
        $model = $this->createModelByClassUid($hit->class_uid);
        return $model->find($hit->private_key);
    }

    /**
     * Get all models by query hits.
     *
     * @param QueryHit[] $hits
     * @return array
     */
    public function models($hits)
    {
        // Get models from hits.
        $results = array_map(function ($hit) {
            return $this->model($hit);
        }, $hits);

        // Skip empty.
        $results = array_filter($results, function ($model) {
            return !is_null($model);
        });

        return $results;
    }
}
