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
     * List of cached query totals.
     *
     * @var array
     */
    private $cachedCounts;

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
     * Run query and get all finding models.
     *
     * @param $query
     * @param $options
     * @return array
     */
    public function models($query, array $options = [])
    {
        $totalCount = null;

        $hits = $this->run($query);
        $models = $this->search->config()->models($hits, $options, $totalCount);
        // Remember total number of results.
        $this->setCachedCount($query, $totalCount);

        return $models;
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
     * Get count of results for query.
     *
     * @param $query
     * @return null|int
     */
    public function getCachedCount($query)
    {
        $hash = $this->hash($query);
        return isset($this->cachedCounts[$hash]) ? $this->cachedCounts[$hash] : null;
    }

    /**
     * Set count of results for query.
     *
     * @param $query
     * @param $count
     */
    public function setCachedCount($query, $count)
    {
        $hash = $this->hash($query);
        $this->cachedCounts[$hash] = $count;
    }

    /**
     * Get hash for query.
     *
     * @param $query
     * @return string
     */
    private function hash($query)
    {
        return md5(serialize($query));
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
