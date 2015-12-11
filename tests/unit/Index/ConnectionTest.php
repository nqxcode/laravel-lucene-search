<?php namespace tests\unit\Index;

use \Mockery as m;

use Nqxcode\LuceneSearch\Index\Connection;
use tests\TestCase;

class ConnectionTest extends TestCase
{
    private $indexPath;
    private $analyzerConfig;

    public function setUp()
    {
        parent::setUp();
        $this->indexPath = sys_get_temp_dir() . '/temp_lucene_index';
        $this->analyzerConfig = m::mock('Nqxcode\LuceneSearch\Analyzer\Config');
        $this->analyzerConfig->shouldReceive('setDefaultAnalyzer');
    }

    public function tearDown()
    {
        parent::tearDown();

        \File::deleteDirectory($this->indexPath);
    }

    public function testCreateIndex()
    {
        $index = $this->createConnection();
        $this->assertNotEmpty($index->getIndex());
        $this->assertEquals($this->indexPath, $index->getIndexPath());
    }

    private function createConnection()
    {
        return new Connection($this->indexPath, $this->analyzerConfig);
    }
}
