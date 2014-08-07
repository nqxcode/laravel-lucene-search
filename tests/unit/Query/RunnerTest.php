<?php namespace tests\unit\Query;

use tests\TestCase;
use Mockery as m;

/**
 * Class RunnerTest
 * @package tests\unit\Query
 */
class RunnerTest extends TestCase
{
    /** @var \Nqxcode\LaravelSearch\Query\Runner */
    private $runner;
    /** @var  \Mockery\MockInterface */
    private $search;

    public function setUp()
    {
        parent::setUp();

        $this->search = m::mock('Nqxcode\LaravelSearch\Search');
        $this->app->instance('Nqxcode\LaravelSearch\Search', $this->search);
        $this->runner = $this->app->make('Nqxcode\LaravelSearch\Query\Runner');

        $this->search->shouldReceive('index->find')->with('test')->andReturn([1, 2, 3, 4, 5])->byDefault();
    }

    public function testRun()
    {
        $this->assertEquals([1, 2, 3, 4, 5], $this->runner->run('test'));
        $this->assertEquals([4, 5], $this->runner->run('test', ['limit' => 2, 'offset' => 3]));
        $this->assertEquals(5, $this->runner->getCachedCount('test'));
        $this->assertEquals(0, $this->runner->getCachedCount('other test'));
        $this->assertEquals('test', $this->runner->getLastQuery());
    }

    public function testModels()
    {
        $this->search->shouldReceive('config->models')->with([1, 2, 3, 4, 5])->andReturn('models')->byDefault();
        $this->assertEquals('models', $this->runner->models('test'));
        $this->search->shouldReceive('config->models')->with([4, 5])->andReturn('models');
        $this->assertEquals('models', $this->runner->models('test', ['limit' => 2, 'offset' => 3]));
    }

    public function testDelete()
    {
        $model = m::mock('Illuminate\Database\Eloquent\Model');
        $this->search->shouldReceive('delete')->with($model)->once();
        $this->runner->delete($model);
    }
}