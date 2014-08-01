<?php
namespace tests\unit\Highlighting;

use Nqxcode\LaravelSearch\Highlighting\Html;
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
        $this->queryRunner = m::mock('Nqxcode\LaravelSearch\QueryRunner');
        $this->highlighter = m::mock('Nqxcode\LaravelSearch\Highlighting\Highlighter');
        $this->analyzerConfig = m::mock('Nqxcode\LaravelSearch\Analyzer\Config');

        $this->html = new Html($this->queryRunner, $this->highlighter, $this->analyzerConfig);
    }

    public function testHighlightMatches()
    {
        $this->queryRunner->shouldReceive('getLastQuery')
            ->andReturn($lastQuery = m::mock())->ordered();

        $this->analyzerConfig->shouldReceive('setAnalyzerForHighlighter')->once()->ordered();
        $lastQuery->shouldReceive('htmlFragmentHighlightMatches')
            ->with('test', 'utf-8', $this->highlighter)->andReturn('highlighted')->ordered();
        $this->analyzerConfig->shouldReceive('setDefaultAnalyzer')->once()->ordered();

        $this->assertEquals('highlighted', $this->html->highlightMatches('test'));
    }

    public function testHighlightMatchesLastQueryIsEmpty()
    {
        $this->queryRunner->shouldReceive('getLastQuery')
            ->andReturn(false);
        $this->analyzerConfig->shouldReceive('setAnalyzerForHighlighter')->never();
        $this->analyzerConfig->shouldReceive('setDefaultAnalyzer')->never();

        $this->assertEquals('test', $this->html->highlightMatches('test'));
    }
} 