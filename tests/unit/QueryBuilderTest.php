<?php namespace tests\unit;

use Mockery as m;

use Nqxcode\LaravelSearch\QueryRunner;
use tests\TestCase;

class QueryBuilderTest extends TestCase
{
    /**
     * @var QueryRunner
     */
    private $queryRunner;

    public function setUp()
    {
        parent::setUp();
        $search = m::mock('Nqxcode\LaravelSearch\Search');
        \App::instance('Nqxcode\LaravelSearch\Search', $search);
        $this->queryRunner = \App::make('Nqxcode\LaravelSearch\QueryRunner');
    }

    public function testQueryRunner()
    {

    }
}
