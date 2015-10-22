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
            'laravel-lucene-search::index.models',
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
        $founded = $query->get();

        $this->assertCount(3, $founded);
        $this->assertEquals('noname pointer', $founded[0]->name);
        $this->assertEquals('broken pointer', $founded[1]->name);
        $this->assertEquals('laser pointer', $founded[2]->name);
    }

    public function testSearchWithDynamicFieldsBoosting()
    {
        $query = Search::query('laser pointer', ['boosted_name', 'description']);
        $founded = $query->get();

        $this->assertCount(3, $founded);

        $this->assertEquals('laser pointer', $founded[0]->name);
        $this->assertEquals('noname pointer', $founded[1]->name);
        $this->assertEquals('broken pointer', $founded[2]->name);
    }
}