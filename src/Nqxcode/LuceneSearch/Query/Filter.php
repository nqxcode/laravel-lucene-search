<?php namespace Nqxcode\LuceneSearch\Query;

use ZendSearch\Lucene\Search\Query\AbstractQuery;

/**
 * Class Filter
 * @package Nqxcode\LuceneSearch\Query
 */
class Filter
{
    /**
     * List of query filters applying before query running.
     *
     * @var array
     */
    private $filters = [];

    /**
     * Are filers already applied?
     *
     * @var bool
     */
    private $applied;

    /**
     * Add query filter for query customization (each filter applying before query running).
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function add(callable $callable)
    {
        $this->filters[] = $callable;

        return $this;
    }

    /**
     * Execute added callback functions for query modification.
     *
     * @param $query
     * @return mixed
     */
    public function applyFilters(AbstractQuery $query)
    {
        if ($this->applied) { // Prevent multiple executions.
            return $query;
        }

        foreach ($this->filters as $filter) {
            $filter($query);
        }

        $this->applied = true;

        return $query;
    }
}
