<?php namespace tests\functional;

use Search;

/**
 * Class SearchTestWithFilters
 * @package tests\functional
 */
class SearchTestWithFilters extends BaseTestCase
{
    public function testSearchByStopWord()
    {
        $query = Search::find('and', '*', ['phrase' => true]);
        $this->assertEquals(0, $query->count());
    }
} 