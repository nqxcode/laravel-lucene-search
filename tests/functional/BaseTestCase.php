<?php
namespace tests\functional;

use tests\TestCase;
use Config;

/**
 * Class BaseTestCase
 * @package tests\functional
 */
abstract class BaseTestCase extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->configure();
    }

    protected function configure()
    {
        Config::set('laravel-lucene-search::index.path', storage_path() . '/lucene-search/index_' . uniqid());
        Config::set(
            'laravel-lucene-search::index.models',
            [
                'tests\models\Product' => [
                    'fields' => [
                        'name' => ['boost' => 1],
                        'description' => ['boost' => 0.2],
                    ],
                    'optional_attributes' => true
                ]
            ]
        );

        $artisan = $this->app->make('artisan');

        // Call migrations specific to our tests, e.g. to seed the db.
        $artisan->call('migrate', ['--database' => 'testbench', '--path' => '../tests/migrations']);

        // Call rebuild search index.
        $artisan->call('search:rebuild');
    }
}
