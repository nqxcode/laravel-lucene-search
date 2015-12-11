<?php namespace tests\unit;

use Mockery as m;
use Nqxcode\LuceneSearch\Index\Connection;
use Nqxcode\LuceneSearch\Model\Config;
use Nqxcode\LuceneSearch\Search;
use tests\models\DummyModel;
use tests\TestCase;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;
use ZendSearch\Lucene\Search\Query\MultiTerm;
use ZendSearch\Lucene\Index\Term;

class SearchTest extends TestCase
{
    /** @var \Mockery\MockInterface|Connection */
    private $connection;
    /** @var  \Mockery\MockInterface|Config */
    private $config;
    /** @var  DummyModel */
    private $model;

    public function setUp()
    {
        parent::setUp();

        $this->model = new DummyModel;
        $this->model->id = 1;
        $this->model->name = 'test name';

        $this->connection = m::mock('Nqxcode\LuceneSearch\Index\Connection');
        $this->connection->shouldReceive('getIndexPath');

        $this->config = m::mock('Nqxcode\LuceneSearch\Model\Config');
        $this->config->shouldReceive('primaryKeyPair')
            ->with($this->model)
            ->andReturn(['primary_key', 1]);
        $this->config->shouldReceive('classUidPair')
            ->with($this->model)
            ->andReturn(['class_uid', '12345']);
        $this->config->shouldReceive('fields')
            ->with($this->model)
            ->andReturn(['name' => ['boost' => 1]]);

        $this->config->shouldReceive('optionalAttributes')
            ->andReturn(['optional_attribute1' => ['boost' => 1, 'value' => 'optional value']]);

        $this->config->shouldReceive('boost')
            ->andReturn(1);

    }

    public function testUpdate()
    {
        $this->connection->shouldReceive('getIndex')->andReturn($luceneIndex = m::mock());
        $luceneIndex->shouldReceive('addDocument')->with(m::on(function ($arg) {
            $doc = new Document();
            $doc->addField(Field::keyword('primary_key', 1));
            $doc->addField(Field::Keyword('class_uid', '12345'));

            $field = Field::unStored('name', 'test name');
            $field->boost = 1;
            $doc->addField($field);

            $field = Field::unStored('optional_attribute1', 'optional value');
            $field->boost = 1;
            $doc->addField($field);

            $this->assertEquals($doc, $arg);
            return true;
        }))->once();

        $luceneIndex->shouldReceive('find')->with(m::on(function ($arg) {
            $term = new MultiTerm();
            $term->addTerm(new Term(1, 'primary_key'), true);
            $term->addTerm(new Term('12345', 'class_uid'), true);

            $this->assertEquals($term, $arg);
            return true;
        }))->andReturnUsing(function () {
            $hitMock = m::mock();
            $hitMock->id = 10;
            return [$hitMock];
        })->once();

        $luceneIndex->shouldReceive('delete')->with(10)->once();

        $index = $this->createIndex();

        $index->update($this->model);
    }

    public function testDelete()
    {
        $this->connection->shouldReceive('getIndex')->andReturn($luceneIndex = m::mock());

        $luceneIndex->shouldReceive('find')->with(m::on(function ($arg) {
            $term = new MultiTerm();
            $term->addTerm(new Term(1, 'primary_key'), true);
            $term->addTerm(new Term('12345', 'class_uid'), true);

            $this->assertEquals($term, $arg);
            return true;
        }))->andReturnUsing(function () {
            $hitMock = m::mock();
            $hitMock->id = 10;
            return [$hitMock];
        });

        $luceneIndex->shouldReceive('delete')->with(10)->once();

        $index = $this->createIndex();
        $index->delete($this->model);
    }

    private function createIndex()
    {
        return new Search($this->connection, $this->config);
    }
}
