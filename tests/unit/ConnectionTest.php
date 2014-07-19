<?php namespace tests\unit;

use Nqxcode\LaravelSearch\Connection;
use tests\TestCase;

class ConnectionTest extends TestCase
{
    private $indexPath;

    public function setUp()
    {
        parent::setUp();
        $this->indexPath = sys_get_temp_dir() . '/temp_lucene_index';
    }

    protected function tearDown()
    {
        parent::tearDown();

        if (is_dir($this->indexPath)) {
            rmdir_recursive($this->indexPath);
        }
    }

    public function testCreateIndex()
    {
        $index = $this->createConnection();
        $this->assertNotEmpty($index->getIndex());
        $this->assertEquals($this->indexPath, $index->getIndexPath());
    }

    public function testDestroyIndex()
    {
        $index = $this->createConnection();
        $this->assertNotEmpty($index->getIndex());
        $index->destroy();
        $this->assertEmpty($index->getIndex());
        $this->assertFalse(is_dir($this->indexPath));
    }

    private function createConnection()
    {
        return new Connection($this->indexPath);
    }
}
