<?php namespace Nqxcode\LaravelOrmSearch;

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
