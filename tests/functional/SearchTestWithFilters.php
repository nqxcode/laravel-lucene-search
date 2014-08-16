<?php namespace tests\functional;

use Search;
use Config;

/**
 * Class SearchTestWithFilters
 * @package tests\functional
 */
class SearchTestWithFilters extends BaseTestCase
{
    public function testSearchByStopWord()
    {
        $query = Search::find('and', '*');
        $this->assertEquals(0, $query->count());

        $query = Search::find('not', '*');
        $this->assertEquals(0, $query->count());

        $query = Search::find('не и только', '*', ['phrase' => false]);
        $this->assertEquals(0, $query->count());
    }

    public function testMorphologySearch()
    {
        $query = Search::find('clocks', '*');
        $this->assertEquals(3, $query->count());

        $query = Search::find('bigger', '*');
        $this->assertEquals(2, $query->count());

        $query = Search::find('smaller', '*');
        $this->assertEquals(3, $query->count());

        $query = Search::find('поиск тестового товара', '*', ['phrase' => false]);
        $this->assertEquals(1, $query->count());
    }
} 