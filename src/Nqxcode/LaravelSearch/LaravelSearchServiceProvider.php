<?php namespace Nqxcode\LaravelSearch;

use Illuminate\Support\ServiceProvider;
use Nqxcode\LaravelSearch\Connection\Wrapper;
use \Illuminate\Foundation\Application;

use Config;
use Nqxcode\LaravelSearch\Index\Connection;
use Nqxcode\LaravelSearch\Index\Configurator;

class LaravelSearchServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('nqxcode/laravel-search');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('search.index', function ($app) {
            return new Index(
                $app['search.index.connection'],
                $app['search.models.configurator']
            );
        });

        $this->app->bindShared('search.index.path', function ($app) {
            return rtrim(Config::get('laravel-search::index.path'), '/');
        });

        $this->app->bindShared('search.index.connection', function ($app) {
            return new Connection($app['search.index.path']);
        });

        $this->app->bindShared('search.models', function ($app) {
            return Config::get('laravel-search::models');
        });

        $this->app->bindShared('search.models.configurator', function ($app) {
            return new Configurator($app['search.models']);
        });

        $this->app->bindShared('search.query.builder', function ($app) {
            return new Query\Builder($app['search.index']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }
}
