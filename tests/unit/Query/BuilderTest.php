<?php namespace tests\unit\Query;

use Illuminate\Database\Eloquent\Collection;
use tests\TestCase;

use Mockery as m;
use ZendSearch\Lucene\Search\Query\Boolean;

use App;
use Input;

class BuilderTest extends TestCase
{
    /** @var \Nqxcode\LuceneSearch\Query\Builder */
    private $constructor;
    /** @var  Boolean */
    private $luceneQuery;

    /** @var  \Mockery\MockInterface */
    private $runner;
    /** @var  \Mockery\MockInterface */
    private $filter;
    /** @var  \Mockery\MockInterface */
    private $query;
    /** @var  \Mockery\MockInterface */
    private $rawQueryBuilder;

    public function setUp()
    {
        parent::setUp();

        $this->runner = m::mock('Nqxcode\LuceneSearch\Query\Runner');
        $this->filter = m::mock('Nqxcode\LuceneSearch\Query\Filter');
        $this->query = m::mock('ZendSearch\Lucene\Search\Query\Boolean');
        $this->rawQueryBuilder = m::mock('Nqxcode\LuceneSearch\Query\RawQueryBuilder');

        $this->app->instance('Nqxcode\LuceneSearch\Query\Runner', $this->runner);
        $this->app->instance('Nqxcode\LuceneSearch\Query\Filter', $this->filter);
        $this->app->instance('ZendSearch\Lucene\Search\Query\Boolean', $this->query);
        $this->app->instance('Nqxcode\LuceneSearch\Query\RawQueryBuilder', $this->rawQueryBuilder);

        $this->rawQueryBuilder->shouldReceive('build')->with($this->defaultFindOptions())->andReturn(['test', true]);
        $this->rawQueryBuilder->shouldReceive('parse')->with('test')->andReturn($this->luceneQuery = new Boolean)->byDefault();

        $this->query->shouldReceive('addSubquery')->with($this->luceneQuery, true);

        $this->runner->shouldReceive('models')->with($this->query)->andReturn(Collection::make([1, 2, 3]))->byDefault();
        $this->runner->shouldReceive('total')->with($this->query)->andReturn(3)->byDefault();

        $this->runner->shouldReceive('models')->with($this->query, [])->andReturn(Collection::make([1, 2, 3]))->byDefault();
        $this->runner->shouldReceive('total')->with($this->query, [])->andReturn(3)->byDefault();

        $this->runner->shouldReceive('run')->with($this->query)->andReturn([1, 2, 3, 4])->byDefault();

        $this->runner->shouldReceive('setCachedTotal')->byDefault();;
        $this->runner->shouldReceive('setCachedModels')->byDefault();;

        $this->runner->shouldReceive('getCachedTotal')->andReturn(null)->byDefault();
        $this->runner->shouldReceive('getCachedModels')->andReturn(null)->byDefault();


        $this->filter->shouldReceive('applyFilters')->with($this->query);

        $this->constructor = $this->app->make('Nqxcode\LuceneSearch\Query\Builder');
    }

    public function testFind()
    {
        $query = $this->constructor->query('test');

        $this->assertEquals(Collection::make([1, 2, 3]), $query->get());
        $this->assertEquals(3, $query->count());
    }

    public function testWhere()
    {
        $this->rawQueryBuilder->shouldReceive('build')->with($this->defaultWhereOptions())->andReturn(['test', true]);
        $query = $this->constructor->where('field', 'test');

        $this->assertEquals(Collection::make([1, 2, 3]), $query->get());
        $this->assertEquals(3, $query->count());
    }

    public function testCountNotCached()
    {
        $query = $this->constructor->query('test');
        $this->assertEquals(3, $query->count());
    }

    public function testCachedModels()
    {
        $this->runner->shouldReceive('getCachedModels')->andReturn([1, 2, 3, 4, 5]);
        $this->runner->shouldReceive('models')->with($this->query, [])->never();

        $query = $this->constructor->query('test');
        $this->assertEquals([1, 2, 3, 4, 5], $query->get());
    }

    public function testCachedTotal()
    {
        $this->runner->shouldReceive('getCachedTotal')->andReturn(5);
        $this->runner->shouldReceive('total')->with($this->query)->never();

        $query = $this->constructor->query('test');
        $this->assertEquals(5, $query->count());
    }

    private function defaultFindOptions()
    {
        return [
            'field' => '*',
            'value' => 'test',
            'required' => true,
            'prohibited' => false,
            'phrase' => true,
            'fuzzy' => null,
            'proximity' => null,
        ];
    }

    private function defaultWhereOptions()
    {
        return [
            'field' => 'field',
            'value' => 'test',
            'required' => true,
            'prohibited' => false,
            'phrase' => true,
            'fuzzy' => null,
            'proximity' => null,
        ];
    }

    public function testPaginate()
    {
        $query = $this->constructor->query('test');
        $this->runner->shouldReceive('models')
            ->with($this->query, ['limit' => 3, 'offset' => 0])
            ->andReturn(Collection::make([1, 2, 3]))->byDefault();

        $expected = App::make('search.paginator')->make([1, 2], 3, 2);
        $actual = $query->paginate(2);

        $this->assertEquals($expected, $actual);

        $this->runner->shouldReceive('models')
            ->with($this->query, ['limit' => 2, 'offset' => 2])
            ->andReturn(Collection::make([3, 4, 1, 2]))->byDefault();

        $actual = $query->paginate(2, 2);

        $this->assertEquals($expected, $actual);

        $this->runner->shouldReceive('models')
            ->with($this->query, ['limit' => 2, 'offset' => 2])
            ->andReturn(Collection::make([3, 4, 1, 2]))->byDefault();

        $actual = $query->paginate(2, function () { return 2; });

        $this->assertEquals($expected, $actual);
    }

    public function testRawQuery()
    {
        $this->assertEquals($this->constructor, $this->constructor->rawQuery('test'));

        $closure = function(){return 'test';};
        $this->rawQueryBuilder->shouldReceive('parse')->with($closure)->andReturn(new Boolean)->byDefault();
        $this->assertEquals($this->constructor, $this->constructor->rawQuery($closure));

        $this->assertEquals($this->constructor, $this->constructor->rawQuery(new Boolean));
    }
} 