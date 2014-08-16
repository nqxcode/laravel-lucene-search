<?php namespace Nqxcode\LuceneSearch\Analyzer;

use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\AbstractCommon;
use ZendSearch\Lucene\Search\QueryParser;
use Nqxcode\LuceneSearch\Analyzer\Stopwords\FilterFactory;
use App;

/**
 * Class Config
 * @package Nqxcode\LuceneSearch\Analyzer
 */
class Config
{
    /** @var array */
    private $filterClasses;
    /** @var array */
    private $stopWordFiles;

    /** @var \Nqxcode\LuceneSearch\Analyzer\Stopwords\FilterFactory */
    private $stopwordsFilterFactory;

    public function __construct(array $filterClasses, array $stopWordFiles, FilterFactory $stopwordsFilterFactory)
    {
        QueryParser::setDefaultEncoding('utf-8');

        $this->filterClasses = $filterClasses;
        $this->stopWordFiles = $stopWordFiles;
        $this->stopwordsFilterFactory = $stopwordsFilterFactory;
    }

    /**
     * Set default analyzer for indexing.
     */
    public function setDefaultAnalyzer()
    {
        /** @var AbstractCommon $analyzer */
        $analyzer = App::make('ZendSearch\Lucene\Analysis\Analyzer\Common\AbstractCommon');

        foreach ($this->stopWordFiles as $file) {
            $analyzer->addFilter($this->stopwordsFilterFactory->newInstance($file));
        }

        foreach ($this->filterClasses as $filterClass) {
            $analyzer->addFilter(App::make($filterClass));
        }

        Analyzer::setDefault($analyzer);
    }

    /**
     * Set analyzer for words highlighting (not for indexing).
     */
    public function setHighlighterAnalyzer()
    {
        /** @var AbstractCommon $analyzer */
        $analyzer = App::make('ZendSearch\Lucene\Analysis\Analyzer\Common\AbstractCommon');

        foreach ($this->filterClasses as $filterClass) {
            $analyzer->addFilter(App::make($filterClass));
        }

        Analyzer::setDefault($analyzer);
    }
}
