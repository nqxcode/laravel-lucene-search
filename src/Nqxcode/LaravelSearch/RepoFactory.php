<?php namespace Nqxcode\LaravelSearch;

class RepoFactory
{
    public function create($className)
    {
        $baseClass = 'Illuminate\Database\Eloquent\Model';

        if (!is_subclass_of($className, $baseClass)) {
            throw new \InvalidArgumentException(
                "The class '{$className}' should be "
                . "inherited from '{$baseClass}'."
            );
        }

        return new $className;
    }

    /**
     * Get classUid for object/class.
     *
     * @param $obj
     * @return string
     */
    public function classUid($obj)
    {
        $className = is_object($obj) ? get_class($obj) : $obj;
        return md5($className);
    }
}
