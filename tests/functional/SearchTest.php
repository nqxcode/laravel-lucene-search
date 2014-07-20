<?php namespace tests\functional;

use tests\TestCase;
use \Nqxcode\LaravelSearch\Config as SearchConfig;

class SearchTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // remove search index
        rmdir_recursive($this->app['search.index_path']);

        $this->app->bind('search.models.config', function ($app) {
            return new SearchConfig(
                [
                    'tests\lib\Product' => [
                        'fields' => [
                            'name',
                            'description',
                        ]
                    ]
                ]
            );
        });

        $artisan = $this->app->make('artisan');

        // call migrations specific to our tests, e.g. to seed the db
        $artisan->call('migrate', ['--database' => 'testbench', '--path' => '../tests/migrations']);

        // call rebuid search index
        $artisan->call('search:rebuild-index');
    }

    public function testSearch()
    {
        $chain = \Search::where('name', 'cool'); // //
        ;
        $results = $chain->get();

        $lastQuery = \Search::lastQuery();

        return;
    }
}
