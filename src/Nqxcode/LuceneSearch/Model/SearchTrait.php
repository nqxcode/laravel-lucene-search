<?php namespace Nqxcode\LuceneSearch\Model;

use Search;
/**
 * Class SearchTrait
 * @package Nqxcode\LuceneSearch\Model
 */
trait SearchTrait
{
    /**
     * Set event handlers for updating of search index.
     */
    public static function mountSearchEvents()
    {
        self::saved(function($model){
            Search::update($model);
        });

        self::deleting(function($model){
            Search::delete($model);
        });
    }
}
