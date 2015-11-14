<?php namespace tests\unit\Highlighting;

use Nqxcode\LuceneSearch\Highlighting\Highlighter;
use tests\TestCase;

use Mockery as m;

class HighlighterTest extends TestCase
{
    /** @var  Highlighter */
    private $highlighter;

    public function setUp()
    {
        $this->highlighter = new Highlighter();
    }

    public function testHighlight()
    {
        $docMock = m::mock('ZendSearch\Lucene\Document\Html');
        $docMock->shouldReceive('highlightExtended')->with('test', [$this->highlighter, 'wrapWords'], [])->once();
        $this->highlighter->setDocument($docMock);

        $this->highlighter->highlight('test');
    }

    public function testWrapWords()
    {
        $this->assertEquals('<span class="highlight">test</span>', $this->highlighter->wrapWords('test'));
    }
}
