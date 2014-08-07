<?php namespace Nqxcode\LaravelSearch\Query\Builder;

use ZendSearch\Lucene\Search\Query\AbstractQuery;

/**
 * Class RawBuilder
 * @package Nqxcode\LaravelSearch\Query
 */
class Raw extends AbstractBuilder
{
    /**
     * Build raw query.
     *
     * @param $query
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function rawQuery($query)
    {
        if ($query instanceof AbstractQuery) {
            $this->query = $query;
        } elseif (is_callable($query)) {
            $this->query = $query();
        } elseif (is_string($query)) {
            $this->query = $query;
        } else {
            throw new \InvalidArgumentException(
                "Argument 'query' must be a string or ZendSearch\\Lucene\\Search\\Query\\AbstractQuery instance or " .
                "callable returning a string or ZendSearch\\Lucene\\Search\\Query\\AbstractQuery instance."
            );
        }

        return $this;
    }
} 