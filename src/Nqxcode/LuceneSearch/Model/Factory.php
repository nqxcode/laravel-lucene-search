<?php namespace Nqxcode\LuceneSearch\Model;

use App;

class Factory
{
    /**
     * @param $className
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \InvalidArgumentException
     */
    public function newInstance($className)
    {
        $baseClass = 'Illuminate\Database\Eloquent\Model';

        if (!is_subclass_of($className, $baseClass)) {
            throw new \InvalidArgumentException(
                "The class '{$className}' must be inherited from '{$baseClass}'."
            );
        }

        return App::make($className);
    }

    /**
     * Get class UID for object/class.
     *
     * @param $obj
     * @return string
     * @throws \InvalidArgumentException
     */
    public function classUid($obj)
    {
        return class_uid($obj);
    }
}
