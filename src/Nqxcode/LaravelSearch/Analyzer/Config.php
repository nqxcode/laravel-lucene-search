<?php namespace Nqxcode\LaravelSearch\Analyzer;

use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\AbstractCommon;
use ZendSearch\Lucene\Analysis\TokenFilter\StopWords;
use ZendSearch\Lucene\Search\QueryParser;

use \App;

/**
 * Class Config
 * @package Nqxcode\LaravelSearch\Analyzer
 */
class Config
{
    private $filters;
    private $stopWordFiles;

    public function __construct(array $filerClasses, array $stopWordFiles)
    {
        QueryParser::setDefaultEncoding('utf-8');

        $this->filters = array_map(function ($filer) {
            return new $filer;
        }, $filerClasses);

        $this->stopWordFiles = array_filter($stopWordFiles, function ($stopWordFile) {
            return is_file($stopWordFile);
        });
    }

    public function setDefaultAnalyzer()
    {
        /** @var AbstractCommon $analyzer */
        $analyzer = App::make('search.analyzer');

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
