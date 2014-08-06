<?php namespace Nqxcode\LaravelSearch\Query;

use Input;
use App;

/**
 * Class Builder
 * @package Nqxcode\LaravelSearch\Query
 */
abstract class Builder
{
    private $runner;

    private $limit;
    private $offset;
    protected $query;
    private $filters = [];
    private $isFiltersExecuted;

    public function __construct(Runner $runner)
    {
        $this->runner = $runner;
    }

    /**
     * Use to add filter for query customization.
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function addFilter(callable $callable)
    {
        $this->filters[] = $callable;

        return $this;
    }

    /**
     * Execute added callback functions for modification of query.
     *
     * @return void
     */
    protected function runFilters()
    {
        // Prevent multiple executions.
        if ($this->isFiltersExecuted) {
            return;
        }

        foreach ($this->filters as $callback) {
            if ($query = $callback($this->query)) {
                $this->query = $query;
            }
        }

        $this->isFiltersExecuted = true;
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

        $this->runFilters(); // Modify query if filters were added.

        return $this->runner->models($this->query, $options);
    }

    /**
     * Execute the current query and return the total number of results.
     *
     * @return int
     */
    public function count()
    {
        $this->runFilters();

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
} 