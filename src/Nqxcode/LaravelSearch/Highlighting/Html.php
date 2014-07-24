<?php namespace Nqxcode\LaravelSearch\Highlighting;

use Nqxcode\LaravelSearch\Analyzer\Config as AnalyzerConfig;
use Nqxcode\LaravelSearch\QueryRunner;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;

/**
 * Class Html
 * @package Nqxcode\LaravelSearch
 */
class Html
{
    /**
     * @var QueryRunner
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
        QueryRunner $queryRunner,
        Highlighter $highlighter,
        AnalyzerConfig $analyzerConfig
    ) {
        $this->queryRunner = $queryRunner;
        $this->highlighter = $highlighter;
        $this->analyzerConfig = $analyzerConfig;
    }

    /**
     * Подсветка результата поиска в html-фрагменте
     *
     * @param string $inputHTMLFragment исходный фрагмента html
     * @param string $inputEncoding Кодировка исходного фрагмента html
     * @param string $outputEncoding Кодировка резульрирующего фрагмента html
     * @return string html фрагмент с подсвеченными результатами поиска
     */
    public function highlightMatches($inputHTMLFragment, $inputEncoding = 'utf-8', $outputEncoding = 'utf-8')
    {
        $highlightedHTMLFragment = '';

        $lastQuery = $this->queryRunner->getLastQuery();

        if (!empty($lastQuery)) {
            $this->analyzerConfig->setAnalyzerForHighlighter();

            $highlightedHTMLFragment =
                $lastQuery->htmlFragmentHighlightMatches($inputHTMLFragment, $inputEncoding, $this->highlighter);

            $this->analyzerConfig->setDefaultAnalyzer();


            $highlightedHTMLFragment = mb_convert_encoding($highlightedHTMLFragment, $outputEncoding, 'utf-8');
        }

        $result = !empty($highlightedHTMLFragment) ? $highlightedHTMLFragment : $inputHTMLFragment;

        return $result;
    }
}
