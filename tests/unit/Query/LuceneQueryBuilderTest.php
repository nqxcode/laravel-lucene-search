<?php namespace tests\unit\Query;

use tests\TestCase;
use Mockery as m;
use ZendSearch\Lucene\Search\QueryParser;

/**
 * Class RunnerTest
 * @package tests\unit\Query
 */
class RawQueryBuilderTest extends TestCase
{
    /** @var \Nqxcode\LuceneSearch\Query\RawQueryBuilder */
    private $builder;

    public function setUp()
    {
        parent::setUp();
        $this->builder = $this->app->make('Nqxcode\LuceneSearch\Query\RawQueryBuilder');
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
            [ ['(field1:(value)) AND NOT primary_key:(value) AND NOT class_uid:(value)', null], ['field' => 'field1', 'value' => 'value'] ],
            [ ['(field1:(value) OR field2:(value)) AND NOT primary_key:(value) AND NOT class_uid:(value)', null], ['field' => ['field1', 'field2'], 'value' => 'value'] ],
            [ ['(field1:(value)) AND NOT primary_key:(value) AND NOT class_uid:(value)', null], ['field' => 'field1', 'value' => 'value', 'required' => false] ],
            [ ['(field1:(value)) AND NOT primary_key:(value) AND NOT class_uid:(value)', null], ['field' => 'field1','value' => 'value', 'prohibited' => false] ],
            [ ['(field1:(value)) AND NOT primary_key:(value) AND NOT class_uid:(value)', null], ['field' => 'field1', 'value' => 'value', 'required' => false, 'prohibited' => false] ],
            [ ['(field1:(value)) AND NOT primary_key:(value) AND NOT class_uid:(value)', true], ['field' => 'field1', 'value' => 'value', 'required' => true, 'prohibited' => false] ],
            [ ['(field1:(value)) AND NOT primary_key:(value) AND NOT class_uid:(value)', true], ['field' => 'field1', 'value' => 'value', 'required' => true, 'prohibited' => true] ],
            [ ['(field1:(value)) AND NOT primary_key:(value) AND NOT class_uid:(value)', false], ['field' => 'field1', 'value' => 'value', 'required' => false, 'prohibited' => true] ],
            [ ['(field1:(value)) AND NOT primary_key:(value) AND NOT class_uid:(value)', null], ['field' => 'field1', 'value' => 'value', 'phrase' => false] ],
            [ ['(field1:("value")) AND NOT primary_key:("value") AND NOT class_uid:("value")', null], ['field' => 'field1', 'value' => 'value', 'phrase' => true] ],
            [ ['(field1:(value~)) AND NOT primary_key:(value~) AND NOT class_uid:(value~)', null], ['field' => 'field1', 'value' => 'value', 'fuzzy' => true] ],
            [ ['(field1:(value~0.1)) AND NOT primary_key:(value~0.1) AND NOT class_uid:(value~0.1)', null], ['field' => 'field1', 'value' => 'value', 'fuzzy' => 0.1] ],
            [ ['(field1:("value~0.1")) AND NOT primary_key:("value~0.1") AND NOT class_uid:("value~0.1")', null], ['field' => 'field1', 'value' => 'value', 'phrase' => true, 'fuzzy' => 0.1] ],
            [ ['(field1:("value"~10)) AND NOT primary_key:("value"~10) AND NOT class_uid:("value"~10)', null], ['field' => 'field1', 'value' => 'value', 'proximity' => 10] ],
            [ ['(field1:("value"~10)) AND NOT primary_key:("value"~10) AND NOT class_uid:("value"~10)', null], ['field' => 'field1', 'value' => 'value', 'proximity' => 10, 'phrase' => false] ],
            [ ['(field1:("value~0.1"~10)) AND NOT primary_key:("value~0.1"~10) AND NOT class_uid:("value~0.1"~10)', null], ['field' => 'field1', 'value' => 'value', 'proximity' => 10, 'fuzzy' => 0.1] ],
            [ ['(field1:("value~0.1"~10) OR field2:("value~0.1"~10)) AND NOT primary_key:("value~0.1"~10) AND NOT class_uid:("value~0.1"~10)', null], ['field' => ['field1', 'field2'], 'value' => 'value', 'proximity' => 10, 'fuzzy' => 0.1] ],
        ];
        $data = array_merge($data, $this->getExpectedAndSourceDataForQueryWithSpecialChars());
        $data = array_merge($data, $this->getExpectedAndSourceDataForQueryWithSpecialOperators());
        return $data;
    }

    private function getExpectedAndSourceDataForQueryWithSpecialOperators()
    {
        return [
            [ ['(field1:("test not value")) AND NOT primary_key:("test not value") AND NOT class_uid:("test not value")', null], ['field' => 'field1', 'value' => 'test not value', 'phrase' => true] ],
            [ ['(field1:("test to value")) AND NOT primary_key:("test to value") AND NOT class_uid:("test to value")', null], ['field' => 'field1', 'value' => 'test to value', 'phrase' => true] ],
            [ ['(field1:("test and value")) AND NOT primary_key:("test and value") AND NOT class_uid:("test and value")', null], ['field' => 'field1', 'value' => 'test and value', 'phrase' => true] ],
            [ ['(field1:("test or value")) AND NOT primary_key:("test or value") AND NOT class_uid:("test or value")', null], ['field' => 'field1', 'value' => 'test or value', 'phrase' => true] ],

            [ ['(field1:(test value)) AND NOT primary_key:(test value) AND NOT class_uid:(test value)', null], ['field' => 'field1', 'value' => 'test not value', 'phrase' => false] ],
            [ ['(field1:(test value)) AND NOT primary_key:(test value) AND NOT class_uid:(test value)', null], ['field' => 'field1', 'value' => 'test to value', 'phrase' => false] ],
            [ ['(field1:(test value)) AND NOT primary_key:(test value) AND NOT class_uid:(test value)', null], ['field' => 'field1', 'value' => 'test and value', 'phrase' => false] ],
            [ ['(field1:(test value)) AND NOT primary_key:(test value) AND NOT class_uid:(test value)', null], ['field' => 'field1', 'value' => 'test or value', 'phrase' => false] ],

            [ ['(field1:("test not value"~10)) AND NOT primary_key:("test not value"~10) AND NOT class_uid:("test not value"~10)', null], ['field' => 'field1', 'value' => 'test not value', 'proximity' => 10] ],
            [ ['(field1:("test to value"~10)) AND NOT primary_key:("test to value"~10) AND NOT class_uid:("test to value"~10)', null], ['field' => 'field1', 'value' => 'test to value', 'proximity' => 10] ],
            [ ['(field1:("test and value"~10)) AND NOT primary_key:("test and value"~10) AND NOT class_uid:("test and value"~10)', null], ['field' => 'field1', 'value' => 'test and value', 'proximity' => 10] ],
            [ ['(field1:("test or value"~10)) AND NOT primary_key:("test or value"~10) AND NOT class_uid:("test or value"~10)', null], ['field' => 'field1', 'value' => 'test or value', 'proximity' => 10] ],

            [ ['(field1:(test value)) AND NOT primary_key:(test value) AND NOT class_uid:(test value)', null], ['field' => 'field1', 'value' => 'test not value', 'proximity' => false] ],
            [ ['(field1:(test value)) AND NOT primary_key:(test value) AND NOT class_uid:(test value)', null], ['field' => 'field1', 'value' => 'test to value', 'proximity' => false] ],
            [ ['(field1:(test value)) AND NOT primary_key:(test value) AND NOT class_uid:(test value)', null], ['field' => 'field1', 'value' => 'test and value', 'proximity' => false] ],
            [ ['(field1:(test value)) AND NOT primary_key:(test value) AND NOT class_uid:(test value)', null], ['field' => 'field1', 'value' => 'test or value', 'proximity' => false] ],
        ];
    }

    private function getExpectedAndSourceDataForQueryWithSpecialChars()
    {
        $data = [];
        $special_chars = ['\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':'];
        foreach ($special_chars as $ch) {
            $expected = str_replace($ch, "\\{$ch}", "test {$ch} value");
            $data[] = [["(field1:({$expected})) AND NOT primary_key:({$expected}) AND NOT class_uid:({$expected})", null], ['field' => 'field1', 'value' => "test {$ch} value"]];
        }
        return $data;
    }

    public function testParse()
    {
        $this->assertEquals(QueryParser::parse(''), $this->builder->parse(''));
        $this->assertEquals(QueryParser::parse('test'), $this->builder->parse('test'));
    }
}
