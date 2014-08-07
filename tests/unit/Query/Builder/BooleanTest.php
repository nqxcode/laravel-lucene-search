<?php namespace tests\unit\Query\Builder;

use tests\TestCase;

use Mockery as m;
use ZendSearch\Lucene\Search\Query\Boolean;

class BooleanTest extends TestCase
{
    /** @var \Nqxcode\LaravelSearch\Query\Builder\Boolean */
    private $constructor;

    /** @var  \Mockery\MockInterface */
    private $runner;
    /** @var  \Mockery\MockInterface */
    private $filter;
    /** @var  \Mockery\MockInterface */
    private $query;
    /** @var  \Mockery\MockInterface */
    private $luceneQueryBuilder;

    /** @var  Boolean */
    private $luceneQuery;

    public function setUp()
    {
        parent::setUp();

        $this->runner = m::mock('Nqxcode\LaravelSearch\Query\Runner');
        $this->filter = m::mock('Nqxcode\LaravelSearch\Query\Filter');
        $this->query = m::mock('ZendSearch\Lucene\Search\Query\Boolean');
        $this->luceneQueryBuilder = m::mock('Nqxcode\LaravelSearch\Query\LuceneQueryBuilder');

        $this->app->instance('Nqxcode\LaravelSearch\Query\Runner', $this->runner);
        $this->app->instance('Nqxcode\LaravelSearch\Query\Filter', $this->filter);
        $this->app->instance('ZendSearch\Lucene\Search\Query\Boolean', $this->query);
        $this->app->instance('Nqxcode\LaravelSearch\Query\LuceneQueryBuilder', $this->luceneQueryBuilder);

        $this->luceneQueryBuilder->shouldReceive('build')->with(m::any())->andReturn(['test', true]);
        $this->luceneQueryBuilder->shouldReceive('parse')->with('test')->andReturn($this->luceneQuery = new Boolean);
        $this->query->shouldReceive('addSubquery')->with($this->luceneQuery, true)->once();
        $this->runner->shouldReceive('models')->with($this->query, [])->andReturn([1, 2, 3])->byDefault();
        $this->runner->shouldReceive('run')->with($this->query)->andReturn([1, 2, 3])->byDefault();
        $this->runner->shouldReceive('getCachedCount')->andReturn(null)->byDefault();
        $this->filter->shouldReceive('applyFilters')->with($this->query);

        $this->constructor = $this->app->make('Nqxcode\LaravelSearch\Query\Builder\Boolean');
    }

    public function testFind()
    {
        $query = $this->constructor->find('test');

        $this->assertEquals([1, 2, 3], $query->get());
        $this->assertEquals(3, $query->count());
    }

    public function testCountNotCached()
    {
        $query = $this->constructor->find('test');
        $this->assertEquals(3, $query->count());
    }

    public function testCountCached()
    {
        $this->runner->shouldReceive('getCachedCount')->andReturn(5);
        $this->runner->shouldReceive('run')->with($this->query)->never();

        $query = $this->constructor->find('test');
        $this->assertEquals(5, $query->count());
    }


    private function defaultOptions()
    {
        return [
            'field' => '*',
            'value' => 'test',
            'required' => true,
            'prohibited' => false,
            'phrase' => false,
            'fuzzy' => null,
            'proximity' => null,
        ];
    }
} 