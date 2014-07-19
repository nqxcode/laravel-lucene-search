<?php namespace tests\functional;

use tests\TestCase;

class SearchTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // remove search index
        rmdir_recursive($this->app['search.index_path']);

        $artisan = $this->app->make('artisan');

        // call migrations specific to our tests, e.g. to seed the db
        $artisan->call('migrate', ['--database' => 'testbench', '--path' => '../tests/migrations']);

        // call rebuid search index
        $artisan->call('search:rebuild-index');
    }

    public function testSearch()
    {
        $chain = \Search::find('very cool', ['description', 'name']);
        $results = $chain->get();

        $lastQuery = \Search::lastQuery();

        return;
    }
}
