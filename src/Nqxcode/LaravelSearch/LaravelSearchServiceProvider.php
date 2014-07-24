<?php namespace Nqxcode\LaravelSearch;

use Illuminate\Support\ServiceProvider;
use Nqxcode\LaravelSearch\Config as ModelsConfig;

use Config;

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
        $this->app->singleton('search', function ($app) {
            return new Search(
                $app['search.connection'],
                $app['search.models.config']
            );
        });

        $this->app->bind('Nqxcode\LaravelSearch\Search', function ($app) {
            return $app['search'];
        });

        $this->app->bindShared('search.index_path', function () {
            return rtrim(Config::get('laravel-search::index_path'), '/');
        });

        $this->app->bindShared('search.connection', function ($app) {
            return new Connection($app['search.index_path']);
        });

        $this->app->bindShared('search.models', function () {
            return Config::get('laravel-search::models');
        });

        $this->app->bindShared('search.models.config', function ($app) {
            return new ModelsConfig(
                $app['search.models'],
                $app->make('Nqxcode\LaravelSearch\RepoFactory')
            );
        });

        $this->app->bindShared('command.search.rebuild-index', function ($app) {
            return new Console\RebuildCommand($app['search']);
        });

        $this->app->bindShared('command.search.clear', function () {
            return new Console\ClearCommand;
        });

        $this->commands(array('command.search.rebuild-index', 'command.search.clear'));
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('search', 'command.search.rebuild-index', 'command.search.clear');
    }
}
