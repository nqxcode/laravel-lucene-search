<?php namespace Nqxcode\LuceneSearch\Query;

use Nqxcode\LuceneSearch\Search;
use ZendSearch\Lucene\Search\Query\AbstractQuery;
use ZendSearch\Lucene\Search\QueryHit;

/**
 * Class Runner
 * @package Nqxcode\LuceneSearch\Query
 */
class Runner
{
    /** @var \Nqxcode\LuceneSearch\Search */
    private $search;

    /**
     * List of cached models for query.
     *
     * @var array
     */
    private $cachedModels;

    /**
     * List of cached totals for query.
     *
     * @var array
     */
    private $cachedTotals;

    /**
     * Last executed query.
     *
     * @var
     */
    private static $lastQuery;

    /**
     * @param Search $search
     */
    public function __construct(Search $search)
    {
        $this->search = $search;
    }

    /**
     * Execute the given query and return the query hits.
     *
     * @param string|AbstractQuery $query
     * @return array|QueryHit
     */
    public function run($query)
    {
        $hits = $this->search->index()->find($query);

        // Remember running query.
        self::$lastQuery = $query;

        return $hits;
    }

    /**
     * Get all finding models.
     *
     * @param $query
     * @param $options
     * @return array
     */
    public function models($query, array $options = [])
    {
        /**
         * Extract models.
         *
         * @var $models
         */
        extract($this->parsed($query, $options));
        return $models;
    }

    /**
     * Get total count of finding models.
     *
     * @param $query
     * @return mixed
     */
    public function total($query)
    {
        /**
         * Extract total.
         *
         * @var $total
         */
        extract($this->parsed($query));
        return $total;
    }

    /**
     * Get parsed results for hits.
     *
     * @param $query
     * @param array $options
     * @return array
     */
    private function parsed($query, array $options = [])
    {
        $hits = $this->run($query);
        $parsed = $this->search->config()->parse($hits, $options);

        /**
         * Extract models.
         *
         * @var $models
         * @var $total
         */
        extract($parsed);

        // Save parsed results in cache.
        $this->setCachedModels($query, $models, $options);
        $this->setCachedTotal($query, $total);

        return $parsed;
    }

    /**
     * Delete model's document from search index.
     *
     * @param $model
     */
    public function delete($model)
    {
        $this->search->delete($model);
    }

    /**
     * Get cached models for query.
     *
     * @param $query
     * @param $options
     * @return null|int
     */
    public function getCachedModels($query, array $options = [])
    {
        $hash = $this->hash($query, $options);
        return isset($this->cachedModels[$hash]) ? $this->cachedModels[$hash] : null;
    }

    /**
     * Set cached models for query.
     *
     * @param $query
     * @param $models
     * @param $options
     */
    public function setCachedModels($query, $models, $options)
    {
        $hash = $this->hash($query, $options);
        $this->cachedModels[$hash] = $models;
    }

    /**
     * Get cached total for query.
     *
     * @param $query
     * @return null|int
     */
    public function getCachedTotal($query)
    {
        $hash = $this->hash($query);
        return isset($this->cachedTotals[$hash]) ? $this->cachedTotals[$hash] : null;
    }

    /**
     * Set cached total for query.
     *
     * @param $query
     * @param $total
     */
    public function setCachedTotal($query, $total)
    {
        $hash = $this->hash($query);
        $this->cachedTotals[$hash] = $total;
    }

    /**
     * Get hash for query.
     *
     * @param $query
     * @param array $options
     * @return string
     */
    private function hash($query, array $options = [])
    {
        return md5(serialize($query) . serialize($options));
    }

    /**
     * Get last executed query.
     *
     * @return AbstractQuery
     */
    public static function getLastQuery()
    {
        return self::$lastQuery;
    }
}
