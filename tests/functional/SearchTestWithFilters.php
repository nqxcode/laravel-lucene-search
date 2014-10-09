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
        $query = Search::query('and', '*');
        $this->assertEquals(0, $query->count());

        $query = Search::query('not', '*');
        $this->assertEquals(0, $query->count());

        $query = Search::query('не и только', '*', ['phrase' => false]);
        $this->assertEquals(0, $query->count());
    }

    public function testMorphologySearch()
    {
        $query = Search::query('clocks', '*');
        $this->assertEquals(3, $query->count());

        $query = Search::query('bigger', '*');
        $this->assertEquals(2, $query->count());

        $query = Search::query('smaller', '*');
        $this->assertEquals(3, $query->count());

        $query = Search::query('поиск тестового товара', '*', ['phrase' => false]);
        $this->assertEquals(1, $query->count());
    }
} 