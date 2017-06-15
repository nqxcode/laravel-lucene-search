<?php

if (!function_exists('class_uid')) {
    /**
     * Get class uid for class or object
     *
     * @param $obj
     * @return string
     */
    function class_uid($obj)
    {
        $className = is_object($obj) ? get_class($obj) : $obj;

        if (!class_exists($className, true)) {
            throw new \InvalidArgumentException("Class '{$className}' doesn't not exist.");
        }

        return md5($className);
    }
}
