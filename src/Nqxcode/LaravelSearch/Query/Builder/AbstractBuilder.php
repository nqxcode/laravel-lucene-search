<?php namespace Nqxcode\LaravelSearch\Query\Builder;

use Nqxcode\LaravelSearch\Query\Filter;
use Nqxcode\LaravelSearch\Query\LuceneQueryBuilder;
use Nqxcode\LaravelSearch\Query\Runner;
use Input;
use App;

/**
 * Class AbstractBuilder
 * @package Nqxcode\LaravelSearch\Query
 */
abstract class AbstractBuilder
{
    /** @var \Nqxcode\LaravelSearch\Query\Runner */
    protected $runner;
    /** @var \Nqxcode\LaravelSearch\Query\Filter */
    protected $filter;
    /** @var \Nqxcode\LaravelSearch\Query\LuceneQueryBuilder */
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

    public function __construct(Runner $runner, Filter $filter, LuceneQueryBuilder $queryBuilder)
    {
        $this->runner = $runner;
        $this->filter = $filter;
        $this->queryBuilder = $queryBuilder;
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
     * @return array
     */
    public function get()
    {
        $options = [];

        if ($this->limit) {
            $options['limit'] = $this->limit;
            $options['offset'] = $this->offset;
        }

        $this->filter->applyFilters($this->query); // Modify query if filters were added.

        return $this->runner->models($this->query, $options);
    }

    /**
     * Execute the current query and return the total number of results.
     *
     * @return int
     */
    public function count()
    {
        $this->filter->applyFilters($this->query);

        $count = $this->runner->getCachedCount($this->query);

        if ($count === null) {
            $count = count($this->runner->run($this->query));
        }
        return $count;
    }

    /**
     * Execute the current query and return a paginator for the results.
     *
     * @param int $perPage
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function paginate($perPage = 25)
    {
        $page = intval(Input::get('page', 1));
        $this->limit($perPage, ($page - 1) * $perPage);
        return App::make('paginator')->make($this->get(), $this->count(), $perPage);
    }

    /**
     * Execute the current query and delete all found models from the search index.
     *
     * @return void
     */
    public function delete()
    {
        $models = $this->get();

        foreach ($models as $model) {
            $this->runner->delete($model);
        }
    }

    /**
     * Add filter for constructing query.
     *
     * @param callable $closure
     * @return $this
     */
    public function addFilter(callable $closure)
    {
        $this->filter->add($closure);

        return $this;
    }
} 