<?php namespace tests\unit\Query;

use tests\TestCase;
use Mockery as m;

/**
 * Class RunnerTest
 * @package tests\unit\Query
 */
class RunnerTest extends TestCase
{
    /** @var \Nqxcode\LuceneSearch\Query\Runner */
    private $runner;
    /** @var  \Mockery\MockInterface */
    private $search;

    public function setUp()
    {
        parent::setUp();

        $this->search = m::mock('Nqxcode\LuceneSearch\Search');
        $this->app->instance('Nqxcode\LuceneSearch\Search', $this->search);
        $this->runner = $this->app->make('Nqxcode\LuceneSearch\Query\Runner');

        $this->search->shouldReceive('index->find')->with('test')->andReturn([1, 2, 3, 4, 5]);
    }

    public function testRun()
    {
        $this->assertEquals([1, 2, 3, 4, 5], $this->runner->run('test'));
        $this->assertEquals('test', $this->runner->getLastQuery());
    }

    public function testModels()
    {
        $this->search->shouldReceive('config->parse')->with([1, 2, 3, 4, 5], ['limit' => 2, 'offset' => 3])->andReturn(['models' => [1, 2, 3, 4, 5], 'total' => 5]);
        $this->assertEquals([1, 2, 3, 4, 5], $this->runner->models('test', ['limit' => 2, 'offset' => 3]));

        $this->assertEquals([1, 2, 3, 4, 5], $this->runner->getCachedModels('test', ['limit' => 2, 'offset' => 3]));
        $this->assertEquals(null, $this->runner->getCachedModels('other test', ['limit' => 2, 'offset' => 3]));

        $this->assertEquals(5, $this->runner->getCachedTotal('test'));
        $this->assertEquals(0, $this->runner->getCachedTotal('other test'));
    }

    public function testDelete()
    {
        $model = m::mock('Illuminate\Database\Eloquent\Model');
        $this->search->shouldReceive('delete')->with($model)->once();
        $this->runner->delete($model);
    }
}
