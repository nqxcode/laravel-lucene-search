<?php namespace Nqxcode\LuceneSearch\Model;

use App;

/**
 * Trait SearchTrait
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
        $result = $closure();
        SearchObserver::setEnabled(true);

        return $result;
    }

    public static function search($value, $field = '*', array $options = [])
    {
        $queryBuilder = App::make('Nqxcode\LuceneSearch\Query\Builder');

        $queryBuilder->query($value, $field, $options);
        $queryBuilder->where('class_uid', class_uid(get_called_class()));

        return $queryBuilder;
    }
}
