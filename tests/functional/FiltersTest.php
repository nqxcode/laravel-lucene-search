<?php namespace tests\functional;

use Search;
use Config;

/**
 * Class FiltersTest
 * @package tests\functional
 */
class FiltersTest extends BaseTestCase
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

        $query = Search::query('clocking', '*');

        $query = Search::query('поиск тестового товара', '*', ['phrase' => false]);
        $this->assertEquals(1, $query->count());
    }
}
