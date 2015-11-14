<?php namespace tests\unit\Analyzer;

use Nqxcode\LuceneSearch\Analyzer\Config;
use tests\TestCase;
use Mockery as m;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;

class ConfigTest extends TestCase
{
    /** @var  Config */
    private $config;
    /** @var \Mockery\MockInterface */
    private $analyzer;
    /** @var \Mockery\MockInterface */
    private $stopwordsFilterFactory;

    public function setUp()
    {
        parent::setUp();

        $this->stopwordsFilterFactory = m::mock('Nqxcode\LuceneSearch\Analyzer\Stopwords\FilterFactory');
        $this->analyzer = m::mock('ZendSearch\Lucene\Analysis\Analyzer\Common\AbstractCommon');

        $this->app->instance('ZendSearch\Lucene\Analysis\Analyzer\Common\AbstractCommon', $this->analyzer);

        $this->config = new Config(
            ['filterClass1'],
            ['stopwordPath1'],
            $this->stopwordsFilterFactory
        );
    }

    public function testSetDefaultAnalyzer()
    {
        $this->stopwordsFilterFactory->shouldReceive('newInstance')
            ->with('stopwordPath1')
            ->andReturn($stopwordsFilterMock = m::mock('ZendSearch\Lucene\Analysis\TokenFilter\TokenFilterInterface'));

        $this->app->instance('filterClass1', $tokenFilterMock = m::mock('ZendSearch\Lucene\Analysis\TokenFilter\TokenFilterInterface'));

        $this->analyzer->shouldReceive('addFilter')->with($stopwordsFilterMock)->once()->ordered();
        $this->analyzer->shouldReceive('addFilter')->with($tokenFilterMock)->once()->ordered();

        $this->config->setDefaultAnalyzer();
        $this->assertEquals($this->analyzer, Analyzer::getDefault());
    }

    public function testSetHighlighterAnalyzer()
    {
        $this->app->instance('filterClass1', $tokenFilterMock = m::mock('ZendSearch\Lucene\Analysis\TokenFilter\TokenFilterInterface'));
        $this->analyzer->shouldReceive('addFilter')->with($tokenFilterMock)->once();

        $this->config->setHighlighterAnalyzer();
        $this->assertEquals($this->analyzer, Analyzer::getDefault());
    }
}
