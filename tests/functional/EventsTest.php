<?php namespace tests\functional;

use tests\models\Product;
use Search;
use tests\models\Tool;

/**
 * Class EventsTest
 * @package tests\functional
 */
class EventsTest extends BaseTestCase
{
    public function testCreate()
    {
        $this->assertEquals(0, Search::query('observer')->count());

        $p = new Product;
        $p->name = 'observer';
        $p->publish = 1;
        $p->save();

        $this->assertEquals(1, Search::query('observer')->count());
    }

    public function testUpdate()
    {
        $this->assertEquals(0, Search::query('observer')->count());

        $p = Product::first();
        $p->name = 'observer';
        $p->save();

        $this->assertEquals(1, Search::query('observer')->count());
    }

    public function testDelete()
    {
        $this->assertEquals(0, Search::query('observer')->count());

        $p = Product::first();
        $p->name = 'observer';
        $p->save();

        $this->assertEquals(1, Search::query('observer')->count());

        $p->delete();

        $this->assertEquals(0, Search::query('observer')->count());
    }

    public function testWithoutSyncingToSearch()
    {
        $this->assertEquals(0, Search::query('observer')->count());

        Product::withoutSyncingToSearch(function () {
            $p = Product::first();
            $p->name = 'observer';
            $p->save();
        });

        $this->assertEquals(0, Search::query('observer')->count());
    }

    public function testSearch()
    {
        $this->assertEquals(0, Search::query('observer')->count());

        $p = new Product;
        $p->name = 'observer';
        $p->save();

        $p = new Tool();
        $p->name = 'observer';
        $p->save();

        $this->assertEquals(2, Search::query('observer')->count());

        $this->assertEquals(1, Product::search('observer')->count());
        $this->assertEquals(1, Tool::search('observer')->count());
    }
}
