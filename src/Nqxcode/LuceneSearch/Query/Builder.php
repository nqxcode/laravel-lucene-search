<?php namespace Nqxcode\LuceneSearch\Query;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;
use ZendSearch\Lucene\Search\Query\AbstractQuery;
use ZendSearch\Lucene\Search\Query\Boolean as QueryBoolean;
use App;

/**
 * Class Builder
 * @package Nqxcode\LuceneSearch\Query
 */
class Builder
{
    /** @var \Nqxcode\LuceneSearch\Query\Runner */
    protected $runner;
    /** @var \Nqxcode\LuceneSearch\Query\RawQueryBuilder */
    protected $queryBuilder;

    /** @var int */
    protected $limit;
    /** @var int */
    protected $offset;

    /**
     * Main query.
     *
     * @var mixed
     */
    protected $query;

    public function __construct(Runner $runner, RawQueryBuilder $queryBuilder, QueryBoolean $query)
    {
        $this->runner = $runner;
        $this->queryBuilder = $queryBuilder;
        $this->query = $query;
    }

    /**
     * Limit results for query.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return $this
     */
    public function limit($limit, $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * Execute current query and return list of models.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get()
    {
        $models = $this->runner->getCachedModels($this->query);
        if (null === $models) {
            $models = $this->runner->models($this->query);
            $this->runner->setCachedModels($this->query, $models);
            $this->runner->setCachedTotal($this->query, $models->count());
        }
        if ($this->limit) {
            $models = $models->slice($this->offset, $this->limit);
        }
        return Collection::make($models->reload()->all());
    }

    /**
     * Execute the current query and return the total number of results.
     *
     * @return int
     */
    public function count()
    {
        $total = $this->runner->getCachedTotal($this->query);
        if (null === $total) {
            $total = $this->runner->total($this->query);
            $this->runner->setCachedTotal($this->query, $total);
        }

        return $total;
    }

    /**
     * Execute the current query and return a paginator for the results.
     *
     * @param int $perPage
     * @param int|null $page
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function paginate($perPage = 25, $page = null)
    {
        $page = $page ?: request()->input('page', 1);

        $this->limit($perPage, ($page - 1) * $perPage);
        $models = $this->get()->all();

        $total = $this->count();

        $paginator = new Paginator($models, $total, $perPage);

        return $paginator;
    }

    /**
     * Add subquery to boolean query.
     *
     * @param QueryBoolean $query
     * @param array $options
     * @return QueryBoolean
     * @throws \RuntimeException
     */
    protected function addSubquery($query, array $options)
    {
        list($value, $sign) = $this->queryBuilder->build($options);
        $query->addSubquery($this->queryBuilder->parse($value), $sign);
        return $query;
    }

    /**
     * Add a basic search clause to the query.
     *
     * @param $value
     * @param $field
     * @param array $options - required   : should match (boolean, true by default)
     *                       - prohibited : should not match (boolean, false by default)
     *                       - phrase     : phrase match (boolean, true by default)
     *                       - proximity  : value of distance between words (unsigned integer)
     *                       - fuzzy      : value of fuzzy(float, 0 ... 1)
     * @return $this
     */
    public function query($value, $field = '*', array $options = [])
    {
        $options['field'] = $field;
        $options['value'] = $value;
        $options = $this->defaultOptions($options);

        $this->query = $this->addSubquery($this->query, $options);

        return $this;
    }

    /**
     * Add where clause to the query for search by phrase.
     *
     * @param string $field
     * @param mixed $value
     * @param array $options - field      : field name
     *                       - value      : value to match
     *                       - required   : should match (boolean, true by default)
     *                       - prohibited : should not match (boolean, false by default)
     *                       - phrase     : phrase match (boolean, true by default)
     *                       - proximity  : value of distance between words (unsigned integer)
     **                      - fuzzy      : value of fuzzy(float, 0 ... 1)
     * @return $this
     */
    public function where($field, $value, array $options = [])
    {
        $options['field'] = $field;
        $options['value'] = $value;
        $options = $this->defaultOptions($options);

        $this->query = $this->addSubquery($this->query, $options);

        return $this;
    }

    /**
     * Get default values for options.
     *
     * @param $options
     * @return array
     */
    private function defaultOptions($options)
    {
        return [
            'field' => array_get($options, 'field'),
            'value' => array_get($options, 'value', ''),
            'required' => array_get($options, 'required', true),
            'prohibited' => array_get($options, 'prohibited', false),
            'phrase' => array_get($options, 'phrase', true),
            'fuzzy' => array_get($options, 'fuzzy', null),
            'proximity' => array_get($options, 'proximity', null),
        ];
    }

    /**
     * Build raw query.
     *
     * @param string|AbstractQuery $query
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function rawQuery($query)
    {
        if (is_string($query)) {
            $query = $this->queryBuilder->parse($query);
        } elseif (is_callable($query)) {
            $query = $this->queryBuilder->parse($query());
        }

        if ($query instanceof AbstractQuery) {
            $this->query = $query;
        } else {
            throw new \InvalidArgumentException(
                "Argument 'query' must be a string or ZendSearch\\Lucene\\Search\\Query\\AbstractQuery instance " .
                "or callable returning a string or ZendSearch\\Lucene\\Search\\Query\\AbstractQuery instance."
            );
        }

        return $this;
    }
}
