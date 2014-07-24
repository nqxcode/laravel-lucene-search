<?php namespace tests\unit;

use Mockery as m;

use Nqxcode\LaravelSearch\QueryRunner;
use tests\TestCase;

class QueryBuilderTest extends TestCase
{
    /**
     * @var QueryRunner
     */
    private $queryBulder;

    public function setUp()
    {
        parent::setUp();
        $search = m::mock('Nqxcode\LaravelSearch\Search');
        \App::instance('Nqxcode\LaravelSearch\Search', $search);
        $this->queryBulder = \App::make('Nqxcode\LaravelSearch\QueryRunner');
    }

    public function testEscapeQueryWithSpecialChars()
    {
        $str = $this->queryBulder->escape("+ - && || ! ( ) { } [ ] ^ \" ~ * ? : \\");
        $this->assertEquals("\\+ \\- \\&& \\|| \\! \\( \\) \\{ \\} \\[ \\] \\^ \\\" \\~ \\* \\? \\: \\\\", $str);
    }

    /**
     * @dataProvider providerQueriesWithOperators
     */
    public function testEscapeQueryOperators($query, $expected)
    {
        $actual = $this->queryBulder->escape($query);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function providerQueriesWithOperators()
    {
        return [
            ['apple to cherry', 'apple cherry'],
            ['apple or cherry', 'apple cherry'],
            ['apple and cherry', 'apple cherry'],
            ['apple not cherry', 'apple cherry'],
        ];
    }


}
