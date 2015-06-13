<?php namespace tests\unit\Query;

use Illuminate\Support\Collection;
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
        $this->search->shouldReceive('config->models')->with([1, 2, 3, 4, 5], ['limit' => 2, 'offset' => 3])->andReturn([[1, 2, 3, 4, 5], 5]);
        $this->assertEquals(Collection::make([1, 2, 3, 4, 5]), $this->runner->models('test', ['limit' => 2, 'offset' => 3]));

        $this->assertEquals(5, $this->runner->getCachedCount('test'));
        $this->assertEquals(0, $this->runner->getCachedCount('other test'));
    }

    public function testDelete()
    {
        $model = m::mock('Illuminate\Database\Eloquent\Model');
        $this->search->shouldReceive('delete')->with($model)->once();
        $this->runner->delete($model);
    }
}
