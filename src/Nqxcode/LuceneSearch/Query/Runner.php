<?php namespace Nqxcode\LuceneSearch\Query;

use Nqxcode\LuceneSearch\Search;
use Nqxcode\LuceneSearch\Support\Collection;
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
     * @param mixed $query
     * @return Collection
     */
    public function models($query)
    {
        $hits = $this->run($query);
        return $this->search->config()->models($hits);
    }

    /**
     * Get total count of finding models.
     *
     * @param $query
     * @return mixed
     */
    public function total($query)
    {
        return $this->models($query)->count();
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
     * @return null|int
     */
    public function getCachedModels($query)
    {
        $hash = $this->hash($query);
        return isset($this->cachedModels[$hash]) ? $this->cachedModels[$hash] : null;
    }

    /**
     * Set cached models for query.
     *
     * @param $query
     * @param $models
     */
    public function setCachedModels($query, $models)
    {
        $hash = $this->hash($query);
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
