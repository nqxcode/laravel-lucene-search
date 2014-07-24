<?php namespace Nqxcode\LaravelSearch;

use Illuminate\Database\Eloquent\Model;
use ZendSearch\Lucene\Search\QueryParser;
use ZendSearch\Lucene\Search\Query\Boolean as QueryBoolean;

use App;
use Input;

class QueryRunner
{
    /**
     * @var Search
     */
    private $search;

    /**
     * @var QueryBoolean
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
     * An array of cached query totals to help reduce subsequent count calls.
     *
     * @var array
     */
    private $cached_query_totals;

    /**
     * The last executed query.
     *
     * @var
     */
    private $last_query;

    /**
     * Get the last executed query.
     *
     * @return mixed
     */
    public function getLastQuery()
    {
        return $this->last_query;
    }

    /**
     * @var string[]
     */
    private $last_query_clauses;

    /**
     * @return string[]
     */
    public function getLastQueryClauses()
    {
        return $this->last_query_clauses;
    }

    /**
     * @param Search $search
     * @param QueryBoolean $query
     */
    public function __construct(Search $search, QueryBoolean $query)
    {
        $this->search = $search;
        $this->query = $query;
    }

    /**
     * Add a "andWhere" clause to the given query based on the given condition.
     *
     * @param QueryBoolean $query
     * @param array $condition - field      : name of the field
     *                         - value      : value to match
     *                         - required   : must match (boolean)
     *                         - prohibited : must not match (boolean)
     *                         - phrase     : match as a phrase (boolean)
     *                         - proximity  : value of distance between words (unsigned integer)
     **                        - fuzzy      : fuzziness value (float, 0 - 1)
     * @return QueryBoolean
     */
    public function addCondition(QueryBoolean $query, array $condition)
    {
        $value = trim($this->escape(array_get($condition, 'value')));

        if (array_get($condition, 'phrase') || array_get($condition, 'proximity')) {
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

        if (isset($condition['proximity']) && false !== $condition['proximity']) {
            if (is_integer($condition['proximity']) && $condition['proximity'] > 0) {
                $proximity = $condition['proximity'];
                $value = $value . '~' . $proximity;
            }
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

        $this->last_query_clauses[] = $value;
        $query->addSubquery(QueryParser::parse($value), $sign);

        return $query;
    }

    /**
     * Add a custom callback fn to be called just before the query is executed.
     *
     * @param callable $closure
     *
     * @return $this
     */
    public function addCallback(callable $closure)
    {
        $this->callbacks[] = $closure;

        return $this;
    }

    /**
     * Add a basic where clause to the query. A where clause filter attemtps
     * to match the value you specify as an entire "phrase". It does not
     * guarantee an exact match of the entire field value.
     *
     * @param string $field
     * @param mixed $value
     * @param mixed $options
     *
     * @return $this
     */
    public function where($field, $value, array $options = [])
    {
        $this->query = $this->addCondition($this->query, [
            'field' => $field,
            'value' => $value,
            'required' => array_get($options, 'required', true),
            'prohibited' => array_get($options, 'prohibited', false),
            'phrase' => array_get($options, 'phrase', true),
            'fuzzy' => array_get($options, 'fuzzy', null),
            'proximity' => array_get($options, 'proximity', null),
        ]);

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
     *                       - proximity  : finding words are a within a specific distance (unsigned integer)
     * @return $this
     */
    public function find($value, $field = '*', array $options = [])
    {
        $this->query = $this->addCondition($this->query, [
            'field' => $field,
            'value' => $value,
            'required' => array_get($options, 'required', true),
            'prohibited' => array_get($options, 'prohibited', false),
            'phrase' => array_get($options, 'phrase', false),
            'fuzzy' => array_get($options, 'fuzzy', null),
            'proximity' => array_get($options, 'proximity', null),
        ]);

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
            $this->search->delete($result);
        }
    }

    /**
     * Execute the current query and return a paginator for the results.
     *
     * @param int $perPage
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function paginate($perPage = 15)
    {
        $paginator = App::make('paginator');

        $page = (int)Input::get('page', 1);

        $this->limit($perPage, ($page - 1) * $perPage);

        return $paginator->make($this->get(), $this->count(), $perPage);
    }

    /**
     * Execute the current query and return the total number of results.
     *
     * @return int
     */
    public function count()
    {
        $this->executeCallbacks();

        if (isset($this->cached_query_totals[md5(serialize($this->query))])) {
            return $this->cached_query_totals[md5(serialize($this->query))];
        }

        return count($this->executeQuery($this->query));
    }

    /**
     * Execute the current query and return the results.
     *
     * @return Model[]
     */
    public function get()
    {
        $options = [];

        if ($this->limit) {
            $options['limit'] = $this->limit;
            $options['offset'] = $this->offset;
        }

        $this->executeCallbacks();

        // Get found hits.
        $hits = $this->executeQuery($this->query, $options);

        // Convert hits to models.
        return $this->search->config()->models($hits);
    }


    /**
     * Execute any callback functions. Only execute once.
     *
     * @return void
     */
    protected function executeCallbacks()
    {
        static $callbacks_executed;

        // Prevent multiple executions.
        if ($callbacks_executed) {
            return;
        }

        foreach ($this->callbacks as $callback) {
            if ($query = $callback($this->query)) {
                $this->query = $query;
            }
        }

        $callbacks_executed = true;
    }

    /**
     * Execute the given query and return the query hits.
     *
     * @param \ZendSearch\Lucene\Search\Query\Boolean $query
     * @param array $options - limit  : max number of records to return
     *                       - offset : number of records to skip
     * @return array|\ZendSearch\Lucene\Search\QueryHit
     */
    public function executeQuery($query, array $options = [])
    {
        $hits = $this->search->index()->find($query);

        // Remember total number of results.
        $this->cached_query_totals[md5(serialize($query))] = count($hits);

        // Remember running query.
        $this->last_query = $query;

        // Limit results.
        if (isset($options['limit']) && isset($options['offset'])) {
            $hits = array_slice($hits, $options['offset'], $options['limit']);
        }

        return $hits;
    }

    /**
     * Escape special characters for Lucene query.
     *
     * @param string $str
     *
     * @return string
     */
    public function escape($str)
    {
        // List of all special chars.
        $special_chars = ['\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':'];

        // List of query operators.
        $query_operators = ['to', 'or', 'and', 'not'];

        // Escape all special characters.
        foreach ($special_chars as $ch) {
            $str = str_replace($ch, "\\{$ch}", $str);
        }

        // Add spaces to operators.
        $query_operators = array_map(function ($operator) {
            return " {$operator} ";
        }, $query_operators);

        // Remove other operators.
        $str = str_ireplace($query_operators, ' ', $str);

        return $str;
    }
}
