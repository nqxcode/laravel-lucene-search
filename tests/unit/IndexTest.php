<?php namespace unit;

use Nqxcode\LaravelSearch\Index;
use \Mockery as m;
use Illuminate\Database\Eloquent\Model;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;
use ZendSearch\Lucene\Search\Query\MultiTerm;
use ZendSearch\Lucene\Index\Term;

class DummyModel extends Model
{
}

class IndexTest extends \TestCase
{
    /** @var \Mockery\MockInterface */
    private $connection;
    /** @var  \Mockery\MockInterface */
    private $configurator;
    /** @var  DummyModel */
    private $model;

    public function setUp()
    {
        parent::setUp();

        $this->model = new DummyModel;
        $this->model->id = 1;
        $this->model->name = 'test name';

        $this->connection = m::mock('Nqxcode\LaravelSearch\Index\Connection');
        $this->connection->shouldReceive('getIndexPath');

        $this->configurator = m::mock('Nqxcode\LaravelSearch\Index\Configurator');
        $this->configurator->shouldReceive('getModelPrivateKey')
            ->with($this->model)
            ->andReturn(['private_key', 1]);
        $this->configurator->shouldReceive('getModelClassHash')
            ->with($this->model)
            ->andReturn(['class_hash', '12345']);
        $this->configurator->shouldReceive('getModelFields')
            ->with($this->model)
            ->andReturn(['name' => []]);

    }

    public function testUpdate()
    {
        $this->connection->shouldReceive('getIndex')->andReturn($luceneIndex = m::mock());
        $luceneIndex->shouldReceive('addDocument')->with(m::on(function ($arg) {
            $doc = new Document();
            $doc->addField(Field::keyword('private_key', 1));
            $doc->addField(Field::Keyword('class_hash', '12345'));
            $doc->addField(Field::unStored('name', 'test name'));

            $this->assertEquals($doc, $arg);
            return true;
        }))->once();

        $luceneIndex->shouldReceive('find')->with(m::on(function ($arg) {
            $term = new MultiTerm();
            $term->addTerm(new Term(1, 'private_key'), true);
            $term->addTerm(new Term('12345', 'class_hash'), true);

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
            $term->addTerm(new Term(1, 'private_key'), true);
            $term->addTerm(new Term('12345', 'class_hash'), true);

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

    public function testDestroy()
    {
        $this->connection->shouldReceive('destroy')->once();
        $index = $this->createIndex();
        $index->destroy();
    }

    private function createIndex()
    {
        return new Index($this->connection, $this->configurator);
    }
}
