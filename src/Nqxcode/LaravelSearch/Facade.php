<?php namespace Nqxcode\LaravelSearch;

class Facade extends \Illuminate\Support\Facades\Facade
{

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'search.index';
    }
}
