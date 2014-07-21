<?php namespace Nqxcode\LaravelSearch;

class RepoCreator
{

    public function create($className)
    {
        if (!class_exists($className, true)) {
            throw new \InvalidArgumentException(
                "The class '{$className}' shall exist."
            );
        }

        if (!is_subclass_of($className, 'Illuminate\Database\Eloquent\Model')) {
            throw new \InvalidArgumentException(
                "The class '{$className}' shall be "
                . "inherited from 'Illuminate\\Database\\Eloquent\\Model'."
            );
        }

        return new $className;
    }

    /**
     * Get hash for object/class.
     *
     * @param $obj
     * @return string
     */
    public function hash($obj)
    {
        $className = is_object($obj) ? get_class($obj) : $obj;
        return md5($className);
    }
}
