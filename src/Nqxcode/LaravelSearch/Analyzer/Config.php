<?php namespace Nqxcode\LaravelSearch\Analyzer;

use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\AbstractCommon;
use ZendSearch\Lucene\Analysis\TokenFilter\StopWords;

use \App;

/**
 * Class Config
 * @package Nqxcode\LaravelSearch\Analyzer
 */
class Config
{
    private $filters;
    private $stopWordFiles;

    public function __construct(array $filers, array $stopWordFiles)
    {
        $this->filters = $filers;
        $this->stopWordFiles = $stopWordFiles;
    }

    public function setDefaultAnalyzer()
    {
        /** @var AbstractCommon $analyzer */
        $analyzer = \App::make('search.analyzer');

        foreach ($this->stopWordFiles as $stopWordFile) {
            $stopWordsFilter = new StopWords;
            $stopWordsFilter->loadFromFile($stopWordFile);
            $analyzer->addFilter($stopWordsFilter);
        }

        foreach ($this->filters as $filter) {
            $analyzer->addFilter($filter);
        }

        Analyzer::setDefault($analyzer);
    }

    public function setAnalyzerForHighlighter()
    {
        /** @var AbstractCommon $analyzer */
        $analyzer = \App::make('search.analyzer');

        foreach ($this->filters as $filter) {
            $analyzer->addFilter($filter);
        }

        Analyzer::setDefault($analyzer);
    }
}
