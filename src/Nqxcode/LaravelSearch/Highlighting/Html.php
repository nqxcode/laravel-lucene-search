<?php namespace Nqxcode\LaravelSearch\Highlighting;

use Nqxcode\LaravelSearch\Analyzer\Config as AnalyzerConfig;
use Nqxcode\LaravelSearch\Query\Runner;
use Nqxcode\LaravelSearch\QueryRunner;

/**
 * Class Html
 * @package Nqxcode\LaravelSearch
 */
class Html
{
    /**
     * @var Runner
     */
    private $queryRunner;

    /**
     * @var Highlighter
     */
    private $highlighter;

    /**
     * @var AnalyzerConfig
     */
    private $analyzerConfig;

    public function __construct(
        Runner $queryRunner,
        Highlighter $highlighter,
        AnalyzerConfig $analyzerConfig
    ) {
        $this->queryRunner = $queryRunner;
        $this->highlighter = $highlighter;
        $this->analyzerConfig = $analyzerConfig;
    }

    /**
     * Highlight matches in HTML fragment.
     *
     * @param string $inputHTMLFragment
     * @param string $inputEncoding
     * @return string
     */
    public function highlightMatches($inputHTMLFragment, $inputEncoding = 'utf-8')
    {
        $highlightedHTMLFragment = '';

        $lastQuery = $this->queryRunner->getLastQuery();

        if (!empty($lastQuery)) {

            $this->analyzerConfig->setAnalyzerForHighlighter();
            $highlightedHTMLFragment =
                $lastQuery->htmlFragmentHighlightMatches($inputHTMLFragment, $inputEncoding, $this->highlighter);
            $this->analyzerConfig->setDefaultAnalyzer();
        }

        return !empty($highlightedHTMLFragment) ? $highlightedHTMLFragment : $inputHTMLFragment;
    }
}
