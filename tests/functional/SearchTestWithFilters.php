<?php namespace tests\functional;

use Search;

/**
 * Class SearchTestWithFilters
 * @package tests\functional
 */
class SearchTestWithFilters extends BaseTestCase
{
    // TODO add more tests.

    public function testSearchByStopWord()
    {
        $query = Search::find('and', '*');
        $this->assertEquals(0, $query->count());
    }
} 