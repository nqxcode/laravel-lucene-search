<?php namespace tests\functional;

use Illuminate\Support\Facades\Artisan;
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

        // Call migrations specific to our tests, e.g. to seed the db.
        Artisan::call('migrate', ['--database' => 'testbench', '--path' => '../tests/migrations']);

        // Call rebuild search index.
        Artisan::call('search:rebuild');
    }

    protected function configure()
    {
        Config::set('laravel-lucene-search.index.path', storage_path('app') . '/lucene-search/index_' . uniqid());
        Config::set(
            'laravel-lucene-search.index.models',
            [
                'tests\models\Product' => [
                    'fields' => [
                        'name',
                        'description',
                    ],
                    'optional_attributes' => [
                        'accessor' => 'custom_optional_attributes'
                    ],
                ]
            ]
        );
    }
}
