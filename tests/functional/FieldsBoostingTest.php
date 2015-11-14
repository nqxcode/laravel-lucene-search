<?php namespace functional;

use tests\functional\BaseTestCase;
use Config;
use Search;

/**
 * Class FieldsBoostingTest
 * @package functional
 */
class FieldsBoostingTest extends BaseTestCase
{
    protected function configure()
    {
        parent::configure();

        Config::set(
            'laravel-lucene-search.index.models',
            [
                'tests\models\Product' => [
                    'fields' => [
                        'name' => ['boost' => 0.2],
                        'description' => ['boost' => 0.8], // Boost 'description' field
                    ],
                    'optional_attributes' => [
                        'accessor' => 'custom_optional_attributes'
                    ]
                ]
            ]
        );
    }

    public function testSearchWithFieldsBoosting()
    {
        $query = Search::query('laser pointer', ['name', 'description']);
        $names = $query->get()->lists('name')->all();

        $this->assertCount(3, $names);

        $this->assertEquals([
            'noname pointer',
            'broken pointer',
            'laser pointer'

        ], $names);
    }

    public function testSearchWithDynamicFieldsBoosting()
    {
        $query = Search::query('laser pointer', ['boosted_name', 'description']);
        $names = $query->get()->lists('name')->all();

        $this->assertCount(3, $names);

        $this->assertEquals([
            'laser pointer',
            'noname pointer',
            'broken pointer'

        ], $names);
    }
}
