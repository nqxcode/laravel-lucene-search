<?php namespace tests\unit;

use Mockery as m;

use tests\models\DummyModel;
use tests\models\Product;
use tests\TestCase;
use \Nqxcode\LuceneSearch\Model\Config;

class ConfigTest extends TestCase
{
    /** @var Config */
    private $config;
    /** @var \Mockery\MockInterface */
    private $productRepoMock;
    /** @var \Mockery\MockInterface */
    private $dummyRepoMock;
    /** @var \Mockery\MockInterface */
    private $unknownRepoMock;

    /** @var \Mockery\MockInterface */
    private $productMock;

    public function setUp()
    {
        parent::setUp();

        $configs = $this->getValidConfigs();

        $modelFactory = m::mock('Nqxcode\LuceneSearch\Model\Factory');

        $modelFactory->shouldReceive('newInstance')
            ->with('tests\models\Product')
            ->andReturn($this->productRepoMock = m::mock(new Product));

        $this->productRepoMock->id = 1;
        $this->productRepoMock->shouldReceive('newInstance')->andReturn($this->productMock = m::mock(new Product));


        $modelFactory->shouldReceive('newInstance')
            ->with('tests\models\DummyModel')
            ->andReturn($this->dummyRepoMock = m::mock(new DummyModel));

        $this->dummyRepoMock->pk = 2;

        $modelFactory->shouldReceive('classUid')->with('tests\models\Product')->andReturn('1');
        $modelFactory->shouldReceive('classUid')->with('tests\models\DummyModel')->andReturn('2');

        $this->unknownRepoMock = m::mock('Illuminate\Database\Eloquent\Model');
        $modelFactory->shouldReceive('classUid')->with(get_class($this->unknownRepoMock))->andReturn('999');

        $this->config = new Config($configs, $modelFactory);
    }

    public function testModels()
    {
        $models = $this->config->modelRepositories();
        $this->assertEquals($this->productRepoMock, $models[0]);
        $this->assertEquals($this->dummyRepoMock, $models[1]);
    }

    public function testModel()
    {
        $hit = m::mock('ZendSearch\Lucene\Search\QueryHit');
        $hit->class_uid = '1';
        $hit->primary_key = 1;

        $this->productMock->shouldReceive('getKeyName')->andReturn('id');
        $this->productMock->shouldReceive('setAttribute')->with('id', 1)->once();

        $this->config->model($hit);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Can't find class for classUid: '999'
     */
    public function testModelWithIncorrectClassUid()
    {
        $hit = m::mock('ZendSearch\Lucene\Search\QueryHit');
        $hit->class_uid = '999';
        $this->config->model($hit);
    }

    public function testFields()
    {
        $fields = $this->config->fields($this->productRepoMock);
        $this->assertEquals(['name' => ['boost' => 1], 'description' => ['boost' => 1]], $fields);

        $fields = $this->config->fields($this->dummyRepoMock);
        $this->assertEquals(['first_field' => ['boost' => 1], 'second_field' => ['boost' => 1]], $fields);
    }

    public function testClassUid()
    {
        $pair = $this->config->classUidPair($this->productRepoMock);
        $this->assertEquals(['class_uid', '1'], $pair);

        $pair = $this->config->classUidPair($this->dummyRepoMock);
        $this->assertEquals(['class_uid', '2'], $pair);
    }

    public function testPrivateKey()
    {
        $pair = $this->config->primaryKeyPair($this->productRepoMock);
        $this->assertEquals(['primary_key', 1], $pair);

        $pair = $this->config->primaryKeyPair($this->dummyRepoMock);
        $this->assertEquals(['primary_key', 2], $pair);
    }

    public function testPrivateKeyForIncorrectModel()
    {
        $message = "Configuration doesn't exist for model of class '" . get_class($this->unknownRepoMock) . "'.";
        $this->setExpectedException('\InvalidArgumentException', $message);

        $this->config->primaryKeyPair($this->unknownRepoMock);
    }

    private function getValidConfigs()
    {
        return [
            'tests\models\Product' => [
                'fields' => [
                    'name',
                    'description',
                ]
            ]
            ,
            'tests\models\DummyModel' => [
                'primary_key' => 'pk',
                'fields' => [
                    'first_field',
                    'second_field',
                ]
            ]
        ];
    }
}
