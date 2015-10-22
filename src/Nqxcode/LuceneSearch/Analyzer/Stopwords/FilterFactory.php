<?php namespace Nqxcode\LuceneSearch\Analyzer\Stopwords;

use ZendSearch\Lucene\Analysis\TokenFilter\StopWords as StopWordsFilter;

/**
 * Class FilterFactory
 * @package Nqxcode\LuceneSearch\Analyzer
 */
class FilterFactory
{
    public function newInstance($path)
    {
        if (!is_file($path)) {
            throw new \InvalidArgumentException("File '{$path}' with stop words doesn't exist.");
        }

        $stopWordsFilter = new StopWordsFilter;
        $stopWordsFilter->loadFromFile($path);
        return $stopWordsFilter;
    }
}
