<?php namespace Nqxcode\LaravelSearch;

use Illuminate\Database\Eloquent\Model;
use \ZendSearch\Lucene\Search\QueryHit;

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
    public function __construct(array $configuration, RepoCreator $repositoryCreator)
    {
        $this->repositoryCreator = $repositoryCreator;

        if (count($configuration) == 0) {
            throw new \InvalidArgumentException('No models found in configuration.');
        }

        foreach ($configuration as $className => $options) {

            $fields = array_get($options, 'fields', []);

            if (count($fields) == 0) {
                throw new \InvalidArgumentException(
                    "For the class '{$className}' parameter 'fields' shall be specified."
                );
            }

            $repo = $repositoryCreator->create($className);

            $this->configuration[] = [
                'repository' => $repo,
                'class_uid' => $repositoryCreator->hash($repo),
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
        $hash = $this->repositoryCreator->hash($model);

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

        throw new \InvalidArgumentException("Can't find class for hash: '{$classUid}'.");
    }

    /**
     * Get list of models instances.
     *
     * @return Model[]
     */
    public function models()
    {
        $models = [];
        foreach ($this->configuration as $config) {
            $models[] = $config['repository'];
        }
        return $models;
    }

    /**
     * Get private key for model.
     *
     * @param Model $model
     * @return array
     */
    public function privateKey(Model $model)
    {
        $c = $this->config($model);
        return ['private_key', $model->{$c['private_key']}];
    }

    /**
     * Get UID for model class.
     *
     * @param Model $model
     * @return array
     */
    public function classUid(Model $model)
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
}
