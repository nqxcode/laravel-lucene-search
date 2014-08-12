<?php namespace tests\unit;

use Mockery as m;

use tests\lib\DummyModel;
use tests\lib\Product;
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

    public function setUp()
    {
        parent::setUp();

        $configs = $this->getValidConfigs();

        $modelFactory = m::mock('Nqxcode\LuceneSearch\Model\Factory');

        $modelFactory->shouldReceive('newInstance')
            ->with('tests\lib\Product')
            ->andReturn($this->productRepoMock = m::mock(new Product));

        $this->productRepoMock->id = 1;

        $modelFactory->shouldReceive('newInstance')
            ->with('tests\lib\DummyModel')
            ->andReturn($this->dummyRepoMock = m::mock(new DummyModel));

        $this->dummyRepoMock->pk = 2;

        $modelFactory->shouldReceive('classUid')->with('tests\lib\Product')->andReturn('1');
        $modelFactory->shouldReceive('classUid')->with('tests\lib\DummyModel')->andReturn('2');

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
        $hit->private_key = 1;
        $this->productRepoMock->shouldReceive('find')->with(1)->once();

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
        $this->assertEquals(['name', 'description'], $fields);

        $fields = $this->config->fields($this->dummyRepoMock);
        $this->assertEquals(['first_field', 'second_field'], $fields);
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
        $pair = $this->config->privateKeyPair($this->productRepoMock);
        $this->assertEquals(['private_key', 1], $pair);

        $pair = $this->config->privateKeyPair($this->dummyRepoMock);
        $this->assertEquals(['private_key', 2], $pair);
    }

    public function testPrivateKeyForIncorrectModel()
    {
        $message = "Configuration doesn't exist for model of class '" . get_class($this->unknownRepoMock) . "'.";
        $this->setExpectedException('\InvalidArgumentException', $message);

        $this->config->privateKeyPair($this->unknownRepoMock);
    }

    private function getValidConfigs()
    {
        return [
            'tests\lib\Product' => [
                'fields' => [
                    'name',
                    'description',
                ]
            ]
            ,
            'tests\lib\DummyModel' => [
                'private_key' => 'pk',
                'fields' => [
                    'first_field',
                    'second_field',
                ]
            ]
        ];
    }
}
