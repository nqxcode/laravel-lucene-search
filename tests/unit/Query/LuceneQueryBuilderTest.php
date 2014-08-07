<?php namespace tests\unit\Query;

use tests\TestCase;
use Mockery as m;
use ZendSearch\Lucene\Search\QueryParser;

/**
 * Class RunnerTest
 * @package tests\unit\Query
 */
class LuceneQueryBuilderTest extends TestCase
{
    /** @var \Nqxcode\LaravelSearch\Query\LuceneQueryBuilder */
    private $builder;

    public function setUp()
    {
        parent::setUp();
        $this->builder = $this->app->make('Nqxcode\LaravelSearch\Query\LuceneQueryBuilder');
    }

    /**
     * @dataProvider getBuildDataProvider
     */
    public function testBuild($expected, $options)
    {
        $actual = $this->builder->build($options);

        $this->assertEquals($expected[0], $actual[0]);
        $this->assertTrue($expected[1] === $actual[1]);
    }

    public function getBuildDataProvider()
    {
        $data = [
            [ ['field1:(value)', null], ['field' => 'field1', 'value' => 'value'] ],
            [ ['field1:(value) OR field2:(value)', null], ['field' => ['field1', 'field2'], 'value' => 'value'] ],
            [ ['field1:(value)', null], ['field' => 'field1', 'value' => 'value', 'required' => false] ],
            [ ['field1:(value)', null], ['field' => 'field1','value' => 'value', 'prohibited' => false] ],
            [ ['field1:(value)', null], ['field' => 'field1', 'value' => 'value', 'required' => false, 'prohibited' => false] ],
            [ ['field1:(value)', true], ['field' => 'field1', 'value' => 'value', 'required' => true, 'prohibited' => false] ],
            [ ['field1:(value)', true], ['field' => 'field1', 'value' => 'value', 'required' => true, 'prohibited' => true] ],
            [ ['field1:(value)', false], ['field' => 'field1', 'value' => 'value', 'required' => false, 'prohibited' => true] ],
            [ ['field1:(value)', null], ['field' => 'field1', 'value' => 'value', 'phrase' => false] ],
            [ ['field1:("value")', null], ['field' => 'field1', 'value' => 'value', 'phrase' => true] ],
            [ ['field1:(value~)', null], ['field' => 'field1', 'value' => 'value', 'fuzzy' => true] ],
            [ ['field1:(value~0.1)', null], ['field' => 'field1', 'value' => 'value', 'fuzzy' => 0.1] ],
            [ ['field1:("value~0.1")', null], ['field' => 'field1', 'value' => 'value', 'phrase' => true, 'fuzzy' => 0.1] ],
            [ ['field1:("value"~10)', null], ['field' => 'field1', 'value' => 'value', 'proximity' => 10] ],
            [ ['field1:("value"~10)', null], ['field' => 'field1', 'value' => 'value', 'proximity' => 10, 'phrase' => false] ],
            [ ['field1:("value~0.1"~10)', null], ['field' => 'field1', 'value' => 'value', 'proximity' => 10, 'fuzzy' => 0.1] ],
            [ ['field1:("value~0.1"~10) OR field2:("value~0.1"~10)', null], ['field' => ['field1', 'field2'], 'value' => 'value', 'proximity' => 10, 'fuzzy' => 0.1] ],
        ];
        $data = array_merge($data, $this->getExpectedAndSourceDataForQueryWithSpecialChars());
        $data = array_merge($data, $this->getExpectedAndSourceDataForQueryWithSpecialOperators());
        return $data;
    }

    private function getExpectedAndSourceDataForQueryWithSpecialOperators()
    {
        return [
            [ ['field1:("test not value")', null], ['field' => 'field1', 'value' => 'test not value', 'phrase' => true] ],
            [ ['field1:("test to value")', null], ['field' => 'field1', 'value' => 'test to value', 'phrase' => true] ],
            [ ['field1:("test and value")', null], ['field' => 'field1', 'value' => 'test and value', 'phrase' => true] ],
            [ ['field1:("test or value")', null], ['field' => 'field1', 'value' => 'test or value', 'phrase' => true] ],

            [ ['field1:(test value)', null], ['field' => 'field1', 'value' => 'test not value', 'phrase' => false] ],
            [ ['field1:(test value)', null], ['field' => 'field1', 'value' => 'test to value', 'phrase' => false] ],
            [ ['field1:(test value)', null], ['field' => 'field1', 'value' => 'test and value', 'phrase' => false] ],
            [ ['field1:(test value)', null], ['field' => 'field1', 'value' => 'test or value', 'phrase' => false] ],

            [ ['field1:("test not value"~10)', null], ['field' => 'field1', 'value' => 'test not value', 'proximity' => 10] ],
            [ ['field1:("test to value"~10)', null], ['field' => 'field1', 'value' => 'test to value', 'proximity' => 10] ],
            [ ['field1:("test and value"~10)', null], ['field' => 'field1', 'value' => 'test and value', 'proximity' => 10] ],
            [ ['field1:("test or value"~10)', null], ['field' => 'field1', 'value' => 'test or value', 'proximity' => 10] ],

            [ ['field1:(test value)', null], ['field' => 'field1', 'value' => 'test not value', 'proximity' => false] ],
            [ ['field1:(test value)', null], ['field' => 'field1', 'value' => 'test to value', 'proximity' => false] ],
            [ ['field1:(test value)', null], ['field' => 'field1', 'value' => 'test and value', 'proximity' => false] ],
            [ ['field1:(test value)', null], ['field' => 'field1', 'value' => 'test or value', 'proximity' => false] ],
        ];
    }

    private function getExpectedAndSourceDataForQueryWithSpecialChars()
    {
        $data = [];
        $special_chars = ['\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':'];
        foreach ($special_chars as $ch) {
            $expected = str_replace($ch, "\\{$ch}", "test {$ch} value");
            $data[] = [["field1:({$expected})", null], ['field' => 'field1', 'value' => "test {$ch} value"]];
        }
        return $data;
    }

    public function testParse()
    {
        $this->assertEquals(QueryParser::parse(''), $this->builder->parse(''));
        $this->assertEquals(QueryParser::parse('test'), $this->builder->parse('test'));
    }
}