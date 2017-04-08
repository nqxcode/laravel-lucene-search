<?php namespace Nqxcode\LuceneSearch\Model;

use App;

/**
 * Class SearchObserver
 * @package LuceneSearch\Model
 */
class SearchObserver
{
    /** @var bool */
    private static $enabled = true;

    /**
     * @param bool $enabled
     */
    public static function setEnabled($enabled)
    {
        self::$enabled = $enabled;
    }

    public function saved($model)
    {
        if (self::$enabled) {
            App::offsetGet('search')->update($model);
        }
    }

    public function deleting($model)
    {
        if (self::$enabled) {
            App::offsetGet('search')->delete($model);
        }
    }
}
