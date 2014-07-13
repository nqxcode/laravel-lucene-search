<?php namespace functional;

use lib\Product;

class SearchTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();

        $artisan = $this->app->make('artisan');

        // call migrations specific to our tests, e.g. to seed the db
        $artisan->call('migrate', array(
            '--database' => 'testbench',
            '--path' => '../tests/migrations',
        ));

        // remove search index
        rmdir_recursive($this->app['search.index.path']);
    }

    public function testSearch()
    {
        $models = Product::all();

        foreach ($models as $model) {
            \Search::update($model);
        }

        $chain = \Search::search('very cool', ['description'], ['phrase' => 0]);

        $results = $chain->get();

        $lastQuery = \Search::lastQuery();

        return;
    }
}
