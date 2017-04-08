<?php namespace Nqxcode\LuceneSearch\Model;

use App;

/**
 * Class Search
 * @package Nqxcode\LuceneSearch\Model
 */
trait SearchTrait
{
    /**
     * Set event handlers for updating of search index.
     */
    public static function bootSearchTrait()
    {
        self::observe(new SearchObserver);
    }

    public static function withoutSyncingToSearch(\Closure $closure)
    {
        SearchObserver::setEnabled(false);
        $closure();
        SearchObserver::setEnabled(true);
    }
}
