<?php namespace tests\unit\Highlighting;

use Nqxcode\LuceneSearch\Highlighting\Html;
use tests\TestCase;

use Mockery as m;
/**
 * Class HtmlTest
 * @package tests\unit\Highlighting
 */
class HtmlTest extends TestCase
{
    /** @var Html */
    private $html;

    /** @var \Mockery\MockInterface */
    private $queryRunner;
    /** @var \Mockery\MockInterface */
    private $analyzerConfig;
    /** @var \Mockery\MockInterface */
    private $highlighter;

    public function setUp()
    {
        $this->queryRunner = m::mock('Nqxcode\LuceneSearch\Query\Runner');
        $this->highlighter = m::mock('Nqxcode\LuceneSearch\Highlighting\Highlighter');
        $this->analyzerConfig = m::mock('Nqxcode\LuceneSearch\Analyzer\Config');

        $this->html = new Html($this->queryRunner, $this->highlighter, $this->analyzerConfig);
    }

    public function testHighlight()
    {
        $this->queryRunner->shouldReceive('getLastQuery')
            ->andReturn($lastQuery = m::mock())->ordered();

        $this->analyzerConfig->shouldReceive('setHighlighterAnalyzer')->once()->ordered();
        $lastQuery->shouldReceive('htmlFragmentHighlightMatches')
            ->with('test', 'utf-8', $this->highlighter)->andReturn('highlighted')->ordered();
        $this->analyzerConfig->shouldReceive('setDefaultAnalyzer')->once()->ordered();

        $this->assertEquals('highlighted', $this->html->highlight('test'));
    }

    public function testHighlightLastQueryIsEmpty()
    {
        $this->queryRunner->shouldReceive('getLastQuery')
            ->andReturn(false);
        $this->analyzerConfig->shouldReceive('setHighlighterAnalyzer')->never();
        $this->analyzerConfig->shouldReceive('setDefaultAnalyzer')->never();

        $this->assertEquals('test', $this->html->highlight('test'));
    }
}
