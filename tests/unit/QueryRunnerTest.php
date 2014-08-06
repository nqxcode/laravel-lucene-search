<?php namespace tests\unit;

use Mockery as m;

use Nqxcode\LaravelSearch\QueryRunner;
use tests\TestCase;

use \App;
use ZendSearch\Lucene\Search\Query\Boolean;
use ZendSearch\Lucene\Search\QueryParser;

class QueryRunnerTest extends TestCase
{
    /** @var  \Mockery\MockInterface */
    private $search;
    /** @var \Mockery\MockInterface */
    private $query;
    /** @var QueryRunner */
    private $queryRunner;

    public function setUp()
    {
        parent::setUp();

        $this->search = m::mock('Nqxcode\LaravelSearch\Search');
        $this->query = m::mock('ZendSearch\Lucene\Search\Query\Boolean');

        $this->queryRunner = new QueryRunner($this->search, $this->query);
    }

    public function testRawQuery()
    {
        $this->queryRunner->rawQuery($query = 'test query');
        $this->queryRunner->rawQuery($query = new Boolean());
        $this->queryRunner->rawQuery(function () {
            return 'test query';
        });
        $this->setExpectedException('\InvalidArgumentException');
        $this->queryRunner->rawQuery(123);
    }

    public function testWhereWithDefaultOptions()
    {
        $value = 'test:("test value")';
        $sign = 1;

        $this->query->shouldReceive('addSubquery')->with(m::on(function ($arg) use ($value) {
            $query = QueryParser::parse($value);
            $this->assertEquals($query, $arg);
            return true;
        }), $sign)->once();

        $this->queryRunner->where('test', 'test value');
    }

    /**
     * @dataProvider getWhereWithOptionsDataProvider
     */
    public function testWhere($expected, $source)
    {
        $value = $expected[0];
        $sign = $expected[1];

        $this->query->shouldReceive('addSubquery')->with(m::on(function ($arg) use ($value) {
            $query = QueryParser::parse($value);
            $this->assertEquals($query, $arg);
            return true;
        }), $sign)->once();

        $this->queryRunner->where($source[0], $source[1], $source[2]);
    }

    public function getWhereWithOptionsDataProvider()
    {
        $data = [
            [['test:("test value")', 0], ['test', 'test value', ['required' => false]]],
            [['test:("test value")', 0], ['test', 'test value', ['prohibited' => true]]],
            [['test:("test value")', null], ['test', 'test value', ['required' => false, 'prohibited' => false]]],
            [['test:(test value)', 1], ['test', 'test value', ['phrase' => false]]],
            [['test:("test~0.1 value~0.1")', 1], ['test', 'test value', ['fuzzy' => 0.1]]],
            [['test:("test~ value~")', 1], ['test', 'test value', ['fuzzy' => true]]],
            [['test:("test value")', 1], ['test', 'test value', ['fuzzy' => false]]],
            [['test:(test~0.1 value~0.1)', 1], ['test', 'test value', ['fuzzy' => 0.1, 'phrase' => false]]],
            [['test:("test value"~10)', 1], ['test', 'test value', ['proximity' => 10]]],

            [['field1:("value"~10) OR field2:("value"~10)', 1], [['field1', 'field2'], 'value', ['proximity' => 10]]],
            [['field1:("value~0.1"~10) OR field2:("value~0.1"~10)', 1], [['field1', 'field2'], 'value', ['proximity' => 10, 'fuzzy' => 0.1]]],
        ];

        $data = array_merge($data, $this->getExpectedAndSourceDataForQueryWithSpecialOperators());
        $data = array_merge($data, $this->getExpectedAndSourceDataForQueryWithSpecialChars());

        return $data;
    }

    public function testFindWithDefaultOptions()
    {
        $value = 'test value';
        $sign = 1;

        $this->query->shouldReceive('addSubquery')->with(m::on(function ($arg) use ($value) {
            $query = QueryParser::parse($value);
            $this->assertEquals($query, $arg);
            return true;
        }), $sign)->once()->byDefault();

        $this->queryRunner->find('test value');

        $value = 'test:(test value)';
        $sign = 1;

        $this->query->shouldReceive('addSubquery')->with(m::on(function ($arg) use ($value) {
            $query = QueryParser::parse($value);
            $this->assertEquals($query, $arg);
            return true;
        }), $sign)->once();

        $this->queryRunner->find('test value', ['test']);
    }

    /**
     * @dataProvider getFindWithOptionsDataProvider
     */
    public function testFind($expected, $source)
    {
        $value = $expected[0];
        $sign = $expected[1];

        $this->query->shouldReceive('addSubquery')->with(m::on(function ($arg) use ($value) {
            $query = QueryParser::parse($value);
            $this->assertEquals($query, $arg);
            return true;
        }), $sign)->once();

        $this->queryRunner->find($source[1], $source[0], $source[2]);
    }

    public function getFindWithOptionsDataProvider()
    {
        $data = [
            [['test:(test value)', 0], ['test', 'test value', ['required' => false]]],
            [['test:(test value)', 0], ['test', 'test value', ['prohibited' => true]]],
            [['test:(test value)', null], ['test', 'test value', ['required' => false, 'prohibited' => false]]],
            [['test:(test value)', 1], ['test', 'test value', ['phrase' => false]]],
            [['test:("test value")', 1], ['test', 'test value', ['phrase' => true]]],
            [['test:(test~0.1 value~0.1)', 1], ['test', 'test value', ['fuzzy' => 0.1]]],
            [['test:(test~ value~)', 1], ['test', 'test value', ['fuzzy' => true]]],
            [['test:(test value)', 1], ['test', 'test value', ['fuzzy' => false]]],
            [['test:(test~0.1 value~0.1)', 1], ['test', 'test value', ['fuzzy' => 0.1, 'phrase' => false]]],
            [['test:("test value"~10)', 1], ['test', 'test value', ['proximity' => 10]]],

            [['field1:("value"~10) OR field2:("value"~10)', 1], [['field1', 'field2'], 'value', ['proximity' => 10]]],
            [['field1:("value~0.1"~10) OR field2:("value~0.1"~10)', 1], [['field1', 'field2'], 'value', ['proximity' => 10, 'fuzzy' => 0.1]]],
        ];

        $data = array_merge($data, $this->getExpectedAndSourceDataForQueryWithSpecialOperators());
        $data = array_merge($data, $this->getExpectedAndSourceDataForQueryWithSpecialChars());

        return $data;
    }

    public function testGetForStringRawQuery()
    {
        $this->queryRunner->rawQuery('test query');
        $this->queryRunner->limit(2, 3);

        $this->queryRunner->addFilter(function ($query) {
            return $query . ' first modification';
        });
        $this->queryRunner->addFilter(function ($query) {
            return $query . ' second modification';
        });

        $expectedQuery = 'test query first modification second modification';

        $this->assertionsForTestingOfGettingResults($expectedQuery);
    }

    public function testGetForCallableRawQuery()
    {
        $this->queryRunner->rawQuery(function () {
            return 'test query';
        });
        $this->queryRunner->limit(2, 3);

        $this->queryRunner->addFilter(function ($query) {
            return $query . ' first modification';
        });
        $this->queryRunner->addFilter(function ($query) {
            return $query . ' second modification';
        });

        $expectedQuery = 'test query first modification second modification';

        $this->assertionsForTestingOfGettingResults($expectedQuery);
    }

    public function testGetForZendSearchQueryRawQuery()
    {
        $query = new Boolean;
        $query->addSubquery(QueryParser::parse('test query'));

        $this->queryRunner->rawQuery($query);
        $this->queryRunner->limit(2, 3);

        $this->queryRunner->addFilter(function ($query) {
            return $query->addSubquery(QueryParser::parse('first'));
        });
        $this->queryRunner->addFilter(function ($query) {
            return $query->addSubquery(QueryParser::parse('second'));
        });

        $expectedQuery = new Boolean;
        $expectedQuery->addSubquery(QueryParser::parse('test query'));
        $expectedQuery->addSubquery(QueryParser::parse('first'));
        $expectedQuery->addSubquery(QueryParser::parse('second'));

        $this->assertionsForTestingOfGettingResults($expectedQuery);
    }

    public function testGetForQueryBuiltByConstructor()
    {
       $test = serialize($this->query);

        $this->query->shouldReceive('addSubquery')->with(m::on(function ($arg) {
            $query = QueryParser::parse('test query');
            $this->assertEquals($query, $arg);
            return true;
        }), true)->byDefault();

        $this->queryRunner->find('test query');
        $this->queryRunner->limit(2, 3);

        //$this->query->shouldReceive('addSubquery')->with('first')->andReturn('first')->once();
        //$this->query->shouldReceive('addSubquery')->with('second')->andReturn('second')->once();

        $this->queryRunner->addFilter(function ($query) {
            return $query->addSubquery(QueryParser::parse("test query"), true);
        });
//        $this->queryRunner->addFilter(function ($query) {
//            return $query->addSubquery(m::any());
//        });

        $this->assertionsForTestingOfGettingResults($this->query);
    }

    private function assertionsForTestingOfGettingResults($expectedQuery)
    {
        $this->search->shouldReceive('index->find')
            ->with(m::on(function ($arg) use ($expectedQuery) {
                $this->assertEquals($expectedQuery, $arg);
                return true;
            }))
            ->andReturn($hits = [1, 2, 3, 4, 5])->once();
        $this->search->shouldReceive('config->models')->with([4, 5])->andReturn('test result')->once();

        $this->assertEquals('test result', $this->queryRunner->get());
        $this->assertEquals(5, $this->queryRunner->count());
        $this->assertEquals($expectedQuery, $this->queryRunner->getLastQuery());
    }

    private function getExpectedAndSourceDataForQueryWithSpecialOperators()
    {
        return [

            [['test:("test not value")', 1], ['test', 'test not value', ['phrase' => true]]],
            [['test:("test to value")', 1], ['test', 'test to value', ['phrase' => true]]],
            [['test:("test and value")', 1], ['test', 'test and value', ['phrase' => true]]],
            [['test:("test or value")', 1], ['test', 'test or value', ['phrase' => true]]],

            [['test:(test value)', 1], ['test', 'test not value', ['phrase' => false]]],
            [['test:(test value)', 1], ['test', 'test to value', ['phrase' => false]]],
            [['test:(test value)', 1], ['test', 'test and value', ['phrase' => false]]],
            [['test:(test value)', 1], ['test', 'test or value', ['phrase' => false]]],
        ];
    }

    private function getExpectedAndSourceDataForQueryWithSpecialChars()
    {
        $data = [];
        $special_chars = ['\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':'];
        foreach ($special_chars as $ch) {
            $expected = str_replace($ch, "\\{$ch}", "test {$ch} value");
            $data[] = [["test:({$expected})", 1], ['test', "test {$ch} value", ['phrase' => false]]];
        }
        return $data;
    }
}
