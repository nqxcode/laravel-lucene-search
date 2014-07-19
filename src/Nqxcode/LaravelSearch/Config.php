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
     * Construct the configuration instance.
     *
     * @param $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $this->create($configuration);
    }

    /**
     * Create configuration for models.
     *
     * @param array $configuration
     * @return array
     * @throws \InvalidArgumentException
     */
    private function create(array $configuration)
    {
        foreach ($configuration as $className => $options) {

            if (!class_exists($className, true)) {
                throw new \InvalidArgumentException(
                    "The class '{$className}' shall exist."
                );
            }

            if (!is_subclass_of($className, 'Illuminate\Database\Eloquent\Model')) {
                throw new \InvalidArgumentException(
                    "The class '{$className}' shall be "
                    . " inherited from 'Illuminate\\Database\\Eloquent\\Model'."
                );
            }

            $fields = array_get($options, 'fields', []);

            if (count($fields) == 0) {
                throw new \InvalidArgumentException(
                    "For the class '{$className}' 'fields' shall be specified."
                );
            }


            return [
                'class_name' => $className,
                'class_uid' => $this->hash($className),
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
        $hash = $this->hash(get_class($model));

        foreach ($this->configuration as $config) {
            if ($config['class_uid'] === $hash) {
                return $config;
            }
        }

        throw new \InvalidArgumentException(
            "Configuraton doesn't exist for model of class '" . get_class($model) . "'."
        );
    }

    /**
     * Get hash for value.
     *
     * @param $value
     * @return string
     */
    private function hash($value)
    {
        return md5($value);
    }

    /**
     * Create instance of model by class UID.
     *
     * @param $classUid
     * @return Model
     * @throws \InvalidArgumentException
     */
    private function createInstanceByClassUid($classUid)
    {
        foreach ($this->configuration as $config) {
            if ($config['class_uid'] == $classUid) {
                return new $config['class_name'];
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
            $models[] = new $config['class_name'];
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
        return $this->createInstanceByClassUid($hit->class_uid)->find($hit->private_key);
    }
}
