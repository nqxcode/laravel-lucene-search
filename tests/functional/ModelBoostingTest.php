<?php namespace functional;

use tests\functional\BaseTestCase;
use Config;
use Search;

/**
 * Class FieldsBoostingTest
 * @package functional
 */
class ModelBoostingTest extends BaseTestCase
{
    protected function configure()
    {
        parent::configure();

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
                    'boost' => [
                        'accessor' => 'custom_boost'
                    ],
                ]
            ]
        );
    }

    public function testSearchWithModelBoosting()
    {
        $query = Search::query('lamp', ['name', 'description']);
        $names = $query->get()->lists('name')->all();

        $this->assertCount(3, $names);

        $this->assertEquals([
            'led lamp',
            'dim lamp',
            'bright lamp',
        ], $names);
    }
}
