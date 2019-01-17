<?php namespace tests\functional;

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

        $artisan = $this->app->make('artisan');

        // Call migrations specific to our tests, e.g. to seed the db.
        $artisan->call('migrate', ['--database' => 'testbench', '--path' => '../tests/migrations']);

        // Call rebuild search index.
        $artisan->call('search:rebuild', ['--force' => true]);
    }

    protected function configure()
    {
        Config::set('laravel-lucene-search::index.path',
            sys_get_temp_dir() . '/laravel-lucene-search/index' . uniqid('index-', true));
        Config::set(
            'laravel-lucene-search::index.models',
            [
                'tests\models\Product' => [
                    'fields' => [
                        'name',
                        'description',
                    ],
                    'optional_attributes' => [
                        'accessor' => 'custom_optional_attributes'
                    ],
                ],
                'tests\models\Tool' => [
                    'fields' => [
                        'name',
                        'description',
                    ]
                ]
            ]
        );
    }
}
