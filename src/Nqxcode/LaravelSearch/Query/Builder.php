<?php namespace Nqxcode\LaravelSearch\Query;

use Nqxcode\LaravelSearch\Index;
use ZendSearch\Lucene\Search\QueryParser;
use ZendSearch\Lucene\Search\Query\Boolean;

use App;
use Input;

class Builder
{
    /**
     * @var \Nqxcode\LaravelSearch\Index
     */
    private $index;

    /**
     * @var \Nqxcode\LaravelSearch\Index\Configurator
     */
    private $configurator;

    /**
     * @var \ZendSearch\Lucene\Search\Query\Boolean
     */
    private $query;

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    protected $limit;

    /**
     * The number of records to skip.
     *
     * @var int
     */
    protected $offset;

    /**
     * Any user defined callback functions to help manipulate the raw
     * query instance.
     *
     * @var array
     */
    protected $callbacks = array();

    /**
     * Flag to remember if callbacks have already been executed.
     * Prevents multiple executions.
     *
     * @var bool
     */
    protected $callbacks_executed = false;

    /**
     * An array of stored query totals to help reduce subsequent count calls.
     *
     * @var array
     */
    private $cached_query_totals;

    /**
     * @param Index $index
     */
    public function __construct(Index $index)
    {
        $this->index = $index;
        $this->configurator = $index->configurator();
        $this->query = new Boolean;
    }

    /**
     * Add a search/where clause to the given query based on the given condition.
     * Return the given $query instance when finished.
     *
     * @param \ZendSearch\Lucene\Search\Query\Boolean $query
     * @param array $condition - field      : name of the field
     *                         - value      : value to match
     *                         - required   : must match
     *                         - prohibited : must not match
     *                         - phrase     : match as a phrase
     *                         - fuzzy      : fuzziness value (0 - 1)
     *
     * @return \ZendSearch\Lucene\Search\Query\Boolean
     */
    public function addSubquery(Boolean $query, array $condition)
    {
        $value = trim(lucene_query_escape(array_get($condition, 'value')));
        if (array_get($condition, 'phrase')) {
            $value = '"' . $value . '"';
        }
        if (isset($condition['fuzzy']) && false !== $condition['fuzzy']) {
            $fuzziness = '';
            if (is_numeric($condition['fuzzy']) && $condition['fuzzy'] >= 0 && $condition['fuzzy'] <= 1) {
                $fuzziness = $condition['fuzzy'];
            }

            $words = array();
            foreach (explode(' ', $value) as $word) {
                $words[] = $word . '~' . $fuzziness;
            }
            $value = implode(' ', $words);
        }

        $sign = null;
        if (!empty($condition['required'])) {
            $sign = true;
        } elseif (!empty($condition['prohibited'])) {
            $sign = false;
        }

        $field = array_get($condition, 'field');
        if (empty($field) || '*' === $field) {
            $field = null;
        }

        if (is_array($field)) {
            $values = array();
            foreach ($field as $f) {
                $values[] = trim($f) . ':(' . $value . ')';
            }
            $value = implode(' OR ', $values);
        } elseif ($field) {
            $value = trim(array_get($condition, 'field')) . ':(' . $value . ')';
        }

        $this->last_query = $value;
        $query->addSubquery(QueryParser::parse($value), $sign);

        return $query;
    }

    /**
     * Add a custom callback fn to be called just before the query is executed.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function addCallback($callback)
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * Add a basic where clause to the query. A where clause filter attemtps
     * to match the value you specify as an entire "phrase". It does not
     * guarantee an exact match of the entire field value.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return $this
     */
    public function where($field, $value)
    {
        $this->query = $this->addSubquery($this->query, array(
            'field' => $field,
            'value' => $value,
            'required' => true,
            'phrase' => true,
        ));

        return $this;
    }

    /**
     * Add a basic search clause to the query.
     *
     * @param $value
     * @param $field
     * @param array $options - required   : requires a match (default)
     *                       - prohibited : requires a non-match
     *                       - phrase     : match the $value as a phrase
     *                       - fuzzy      : perform a fuzzy search (true, or numeric between 0-1)
     * @return $this
     */
    public function build($value, $field = '*', array $options = array())
    {
        $this->query = $this->addSubquery($this->query, array(
            'field' => $field,
            'value' => $value,
            'required' => array_get($options, 'required', true),
            'prohibited' => array_get($options, 'prohibited', false),
            'phrase' => array_get($options, 'phrase', false),
            'fuzzy' => array_get($options, 'fuzzy', null),
        ));

        return $this;
    }


    /**
     * Set the "limit" and "offset" value of the query.
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
     * Execute the current query and perform delete operations on each
     * document found.
     *
     * @return void
     */
    public function delete()
    {
        $results = $this->get();

        foreach ($results as $result) {
            $this->index->delete(array_get($result, 'id'));
        }
    }

    /**
     * Execute the current query and return a paginator for the results.
     *
     * @param int $num
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function paginate($num = 15)
    {
        $paginator = App::make('paginator');

        $page = (int)Input::get('page', 1);

        $this->limit($num, ($page - 1) * $num);

        return $paginator->make($this->get(), $this->count(), $num);
    }

    /**
     * Execute the current query and return the total number of results.
     *
     * @return int
     */
    public function count()
    {
        $this->executeCallbacks();

        return $this->runCount($this->query);
    }

    /**
     * Execute the current query and return the results.
     *
     * @return array
     */
    public function get()
    {
        $options = array();
        if ($this->limit) {
            $options['limit'] = $this->limit;
            $options['offset'] = $this->offset;
        }

        $this->executeCallbacks();

        $results = $this->runQuery($this->query, $options);

        return $results;
    }


    /**
     * Execute any callback functions. Only execute once.
     *
     * @return void
     */
    protected function executeCallbacks()
    {
        if ($this->callbacks_executed) {
            return;
        }

        $this->callbacks_executed = true;

        foreach ($this->callbacks as $callback) {
            if ($q = call_user_func($callback, $this->query)) {
                $this->query = $q;
            }
        }
    }

    /**
     * Execute the given query and return the total number of results.
     *
     * @param \ZendSearch\Lucene\Search\Query\Boolean $query
     *
     * @return int
     */
    public function runCount($query)
    {
        if (isset($this->cached_query_totals[md5(serialize($query))])) {
            return $this->cached_query_totals[md5(serialize($query))];
        }

        return count($this->runQuery($query));
    }

    /**
     * Execute the given query and return the results.
     * Return an array of records where each record is an
     * instance of Illuminate\Database\Eloquent\Model class
     *
     * @param \ZendSearch\Lucene\Search\Query\Boolean $query
     * @param array $options - limit  : max # of records to return
     *                       - offset : # of records to skip
     *
     * @return array
     */
    public function runQuery($query, array $options = array())
    {
        $response = $this->index->index()->find($query);

        $this->cached_query_totals[md5(serialize($query))] = count($response);

        $results = array();

        if (!empty($response)) {
            foreach ($response as $hit) {
                $results[] = [
                    'id' => $hit->private_key,
                    'model_instance' => $this->configurator->model($hit->class_hash),
                    'score' => $hit->score,
                ];
            }
        }

        if (isset($options['limit']) && isset($options['offset'])) {
            $results = array_slice($results, $options['offset'], $options['limit']);
        }

        $results = array_map(function ($item) {
            return $item['model_instance']->find($item['id']);
        }, $results);

        $results = array_filter($results, function ($model) {
            return !is_null($model);
        });

        return $results;
    }
}
