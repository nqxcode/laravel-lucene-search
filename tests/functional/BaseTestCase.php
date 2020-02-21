<?php namespace tests\functional;

use File;
use tests\TestCase;
use Config;

/**
 * Class BaseTestCase
 * @package tests\functional
 */
abstract class BaseTestCase extends TestCase
{
    private $indexPath;

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

    public function tearDown()
    {
        parent::tearDown();

        app('search')->destroyConnection();

        File::deleteDirectory($this->indexPath);
    }

    protected function configure()
    {
        $this->indexPath = sys_get_temp_dir() . '/laravel-lucene-search/' . uniqid('index-', true);
        Config::set('laravel-lucene-search::index.path', $this->indexPath);
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
