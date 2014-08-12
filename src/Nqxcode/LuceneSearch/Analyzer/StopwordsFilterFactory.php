<?php namespace Nqxcode\LuceneSearch\Analyzer;

use ZendSearch\Lucene\Analysis\TokenFilter\StopWords;

/**
 * Class StopwordsFilterFactory
 * @package Nqxcode\LuceneSearch\Analyzer
 */
class StopwordsFilterFactory
{
    public function newInstance($path)
    {
        if (!is_file($path)) {
            throw new \InvalidArgumentException("File '{$path}' with stop words doesn't exit.");
        }

        $stopWordsFilter = new StopWords;
        $stopWordsFilter->loadFromFile($path);
        return $stopWordsFilter;
    }

    public static function defaultStopwords()
    {
        $dir = __DIR__ . '/resources/stopwords';
        return ["{$dir}/en", "{$dir}/ru",];
    }
} 