<?php namespace tests\unit\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mockery as m;
use Nqxcode\LuceneSearch\Model\Config;

use tests\models\DummyModel;
use tests\models\Product;
use tests\TestCase;

class ConfigTest extends TestCase
{
    /** @var Config */
    private $config;
    /** @var Model|\Mockery\MockInterface */
    private $productRepoMock;
    /** @var Model|\Mockery\MockInterface */
    private $dummyRepoMock;
    /** @var Model|\Mockery\MockInterface */
    private $unknownRepoMock;

    /** @var Model|\Mockery\MockInterface */
    private $productMock;
    /** @var Model|\Mockery\MockInterface */
    private $dummyMock;

    public function setUp()
    {
        parent::setUp();

        $configs = $this->getValidConfigs();

        /** @var \Nqxcode\LuceneSearch\Model\Factory $modelFactory */
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
        $this->dummyRepoMock->shouldReceive('newInstance')->andReturn($this->dummyMock = m::mock(new DummyModel));

        $modelFactory->shouldReceive('classUid')->with('tests\models\Product')->andReturn('1');
        $modelFactory->shouldReceive('classUid')->with('tests\models\DummyModel')->andReturn('2');

        $this->unknownRepoMock = m::mock('Illuminate\Database\Eloquent\Model');
        $modelFactory->shouldReceive('classUid')->with(get_class($this->unknownRepoMock))->andReturn('999');

        $this->config = new Config($configs, $modelFactory);
    }

    public function testModelRepositories()
    {
        $models = $this->config->repositories();
        $this->assertEquals($this->productRepoMock, $models[0]);
        $this->assertEquals($this->dummyRepoMock, $models[1]);
    }

    public function testPrimaryKeyPair()
    {
        $pair = $this->config->primaryKeyPair($this->productRepoMock);
        $this->assertEquals(['primary_key', 1], $pair);

        $pair = $this->config->primaryKeyPair($this->dummyRepoMock);
        $this->assertEquals(['primary_key', 2], $pair);
    }

    public function testPrimaryKeyPairForIncorrectModel()
    {
        $message = "Configuration doesn't exist for model of class '" . get_class($this->unknownRepoMock) . "'.";
        $this->setExpectedException('\InvalidArgumentException', $message);

        $this->config->primaryKeyPair($this->unknownRepoMock);
    }

    public function testClassUidPair()
    {
        $pair = $this->config->classUidPair($this->productRepoMock);
        $this->assertEquals(['class_uid', '1'], $pair);

        $pair = $this->config->classUidPair($this->dummyRepoMock);
        $this->assertEquals(['class_uid', '2'], $pair);
    }

    public function testFields()
    {
        $fields = $this->config->fields($this->productRepoMock);
        $this->assertEquals(['name' => ['boost' => 1], 'description' => ['boost' => 1]], $fields);

        $fields = $this->config->fields($this->dummyRepoMock);
        $this->assertEquals(['first_field' => ['boost' => 0.1], 'second_field' => ['boost' => 0.2]], $fields);
    }

    public function testOptionalAttributes()
    {
        $this->productMock->shouldReceive('getAttribute')
            ->with('optional_attributes')
            ->andReturn([
                'name' => 'value',
                'boosted_name' => ['boost' => 0.1, 'value' => 'boosted_value']
            ])->byDefault();

        $expected = [
            'name' => ['boost' => 1, 'value' => 'value'],
            'boosted_name' => ['boost' => 0.1, 'value' => 'boosted_value']
        ];
        $this->assertEquals($expected, $this->config->optionalAttributes($this->productMock));


        $this->productMock->shouldReceive('getAttribute')
            ->with('optional_attributes')
            ->andReturn([
                0 => 'value',
                1 => ['boost' => 0.1, 'value' => 'boosted_value']
            ])->byDefault();

        $expected = [
            'optional_attributes_0' => ['boost' => 1, 'value' => 'value'],
            'optional_attributes_1' => ['boost' => 0.1, 'value' => 'boosted_value']
        ];
        $this->assertEquals($expected, $this->config->optionalAttributes($this->productMock));


        $this->dummyMock->shouldReceive('getAttribute')
            ->with('custom_optional_attributes')
            ->andReturn([
                'name' => 'value',
                'boosted_name' => ['boost' => 0.1, 'value' => 'boosted_value']
            ])->byDefault();

        $expected = [
            'name' => ['boost' => 1, 'value' => 'value'],
            'boosted_name' => ['boost' => 0.1, 'value' => 'boosted_value']
        ];
        $this->assertEquals($expected, $this->config->optionalAttributes($this->dummyMock));


        $this->dummyMock->shouldReceive('getAttribute')
            ->with('custom_optional_attributes')
            ->andReturn([
                0 => 'value',
                1 => ['boost' => 0.1, 'value' => 'boosted_value']
            ])->byDefault();

        $expected = [
            'custom_optional_attributes_0' => ['boost' => 1, 'value' => 'value'],
            'custom_optional_attributes_1' => ['boost' => 0.1, 'value' => 'boosted_value']
        ];
        $this->assertEquals($expected, $this->config->optionalAttributes($this->dummyMock));
    }

    public function testBoost()
    {
        $this->productMock->shouldReceive('getAttribute')
            ->with('boost')
            ->andReturn(0.5)->byDefault();

        $this->dummyMock->shouldReceive('getAttribute')
            ->with('custom_boost_accessor')
            ->andReturn(0.3)->byDefault();

        $actual = $this->config->boost($this->productMock);
        $this->assertEquals(0.5, $actual);

        $actual = $this->config->boost($this->dummyMock);
        $this->assertEquals(0.3, $actual);
    }

    public function testModels()
    {
        // Asserts for product model
        list($cKey, $cValue) = $this->config->classUidPair(new Product);
        list($pKey, $pValue) = $this->config->primaryKeyPair(new Product);

        $this->productRepoMock->shouldReceive('searchableIds')->andReturn([1, 2, 3])->byDefault();

        $hitMock = m::mock('ZendSearch\Lucene\Search\QueryHit');
        $hitMock->{$cKey} = $cValue;

        $hitMock->{$pKey} = 1;
        $this->assertEquals([1], $this->config->models([$hitMock])->lists('id')->all());

        $hitMock->{$pKey} = 5;
        $this->assertEquals([], $this->config->models([$hitMock])->lists('id')->all());

        // Asserts for dummy model
        list($cKey, $cValue) = $this->config->classUidPair(new DummyModel);
        list($pKey, $pValue) = $this->config->primaryKeyPair(new DummyModel);

        $this->dummyMock->shouldReceive('newQuery')->andReturn($builderMock = m::mock(Builder::class));
        $builderMock->shouldReceive('lists')->with('pk')->andReturn([1, 2, 3])->byDefault();

        $hitMock = m::mock('ZendSearch\Lucene\Search\QueryHit');
        $hitMock->{$cKey} = $cValue;

        $hitMock->{$pKey} = 3;
        $this->assertEquals([3], $this->config->models([$hitMock])->lists('pk')->all());

        $hitMock->{$pKey} = 10;
        $this->assertEquals([], $this->config->models([$hitMock])->lists('pk')->all());

    }

    private function getValidConfigs()
    {
        return [
            'tests\models\Product' => [
                'fields' => [
                    'name',
                    'description',
                ],
                'optional_attributes' => true,
                'boost' => true,
            ]
            ,
            'tests\models\DummyModel' => [
                'primary_key' => 'pk',
                'fields' => [
                    'first_field' => ['boost' => 0.1],
                    'second_field' => ['boost' => 0.2],
                ],
                'optional_attributes' => [
                    'accessor' => 'custom_optional_attributes'
                ],
                'boost' => [
                    'accessor' => 'custom_boost_accessor'
                ],
            ]
        ];
    }
}
