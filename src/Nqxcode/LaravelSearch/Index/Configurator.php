<?php namespace Nqxcode\LaravelSearch\Index;

use Illuminate\Database\Eloquent\Model;

class Configurator
{
    private $configuration = [];

    private function createConfiguration($class, $options)
    {
        if (!class_exists($class, true)) {
            throw new \InvalidArgumentException(
                "The class '{$class}' specified in 'configuration' recieved in constructor shall exist."
            );
        }

        $reflector = new \ReflectionClass($class);
        if (!$reflector->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
            throw new \InvalidArgumentException(
                "The class '{$class}' specified in 'configuration' recieved in constructor shall be "
                . " inherited from 'Illuminate\\Database\\Eloquent\\Model' class."
            );
        }

        $fields = array_get($options, 'fields', []);

        if (count($fields) == 0) {
            throw new \InvalidArgumentException(
                "For the class '{$class}' specified in 'configuration' must be 'fields'."
            );
        }


        return [
            'class' => $class,
            'class_hash' => $this->hash($class),
            'fields' => $fields,
            'private_key' => array_get($options, 'private_key', 'id')
        ];
    }

    public function __construct($configuration)
    {
        foreach ($configuration as $class => $options) {
            $this->configuration[] = $this->createConfiguration($class, $options);
        }
    }

    /**
     * @param Model $model
     * @return array
     * @throws \InvalidArgumentException
     */
    private function configuration(Model $model)
    {
        $hash = $this->hash(get_class($model));

        foreach ($this->configuration as $config) {
            if ($config['class_hash'] === $hash) {
                return $config;
            }
        }

        throw new \InvalidArgumentException(
            "Configuraton doesn't exist for model of class '" . get_class($model) . "'."
        );
    }

    /**
     * @param $value
     * @return string
     */
    private function hash($value)
    {
        return md5($value);
    }

    /**
     * @param $class_hash
     * @return array
     * @throws \InvalidArgumentException
     */
    public function model($class_hash)
    {
        foreach ($this->configuration as $config) {
            if ($config['class_hash'] == $class_hash) {
                return new $config['class'];
            }
        }

        throw new \InvalidArgumentException("Can't find class for hash: '{$class_hash}'.");
    }

    /**
     * @param Model $model
     * @return array
     */
    public function getModelPrivateKey(Model $model)
    {
        $c = $this->configuration($model);
        return ['private_key', $model->{$c['private_key']}];
    }

    /**
     * @param Model $model
     * @return array
     */
    public function getModelClassHash(Model $model)
    {
        $c = $this->configuration($model);
        return ['class_hash', $c['class_hash']];
    }

    /**
     * @param Model $model
     * @return array
     */
    public function getModelFields(Model $model)
    {
        $c = $this->configuration($model);
        return $c['fields'];
    }
}
