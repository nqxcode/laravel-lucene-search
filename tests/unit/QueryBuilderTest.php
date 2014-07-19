<?php namespace unit;

use Mockery as m;

use Nqxcode\LaravelSearch\QueryBuilder;

class QueryBuilderTest extends \TestCase
{
    /**
     * @var QueryBuilder
     */
    private $queryBulder;

    public function setUp()
    {
        parent::setUp();
        $index = m::mock('Nqxcode\LaravelSearch\Search');
        $index->shouldReceive('config');
        $this->queryBulder = new QueryBuilder($index);
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
