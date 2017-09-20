<?php namespace Nqxcode\LuceneSearch;

/**
 * Class Facade
 * @package Nqxcode\LuceneSearch
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'search';
    }
}
