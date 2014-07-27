<?php namespace Nqxcode\LaravelSearch;

use Illuminate\Database\Eloquent\Model;
use ZendSearch\Lucene\Search\Query\AbstractQuery;
use ZendSearch\Lucene\Search\Query\Boolean as QueryBoolean;
use ZendSearch\Lucene\Search\QueryHit;

use App;
use Input;
use ZendSearch\Lucene\Search\QueryParser;

class QueryRunner
{
    /**
     * @var Search
     */
    private $search;

    /**
     * @var string|AbstractQuery
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
     * Callback functions to help manipulate the raw query instance.
     *
     * @var array
     */
    protected $callbacks = [];

    /**
     * List of cached query totals.
     *
     * @var array
     */
    private $cached_query_totals;

    /**
     * List of clauses of current query.
     *
     * @var string[]
     */
    private $current_query_clauses;

    /**
     * Last executed query.
     *
     * @var
     */
    private static $last_query;

    /**
     * Get last executed query.
     *
     * @return mixed
     */
    public static function getLastQuery()
    {
        return self::$last_query;
    }

    /**
     * List of clauses of last query.
     *
     * @var string[]
     */
    private static $last_query_clauses;

    /**
     * Get list of clauses of last query.
     *
     * @return string[]
     */
    public static function getLastQueryClauses()
    {
        return self::$last_query_clauses;
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
                "Argument 'query' should be a string or ZendSearch\\Lucene\\Search\\Query\\AbstractQuery instance or " .
                "callable returning a string or ZendSearch\\Lucene\\Search\\Query\\AbstractQuery instance."
            );
        }

        return $this;
    }

    /**
     * Use for customize query.
     *
     * @param callable $closure should return
     *
     * @return $this
     */
    public function addCallback(callable $closure)
    {
        $this->callbacks[] = $closure;

        return $this;
    }

    /**
     * Add where clause to the query for phrase search.
     *
     * @param string $field
     * @param mixed $value
     * @param array $options - field      : field name
     *                       - value      : value to match
     *                       - required   : should match (boolean)
     *                       - prohibited : should not match (boolean)
     *                       - phrase     : phrase match (boolean)
     *                       - proximity  : value of distance between words (unsigned integer)
     **                      - fuzzy      : value of fuzzy(float, 0 ... 1)
     * @return $this
     */
    public function where($field, $value, array $options = [])
    {
        $this->query = $this->addSubquery($this->query, [
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
     * @param array $options - required   : should match (boolean)
     *                       - prohibited : should not match (boolean)
     *                       - phrase     : phrase match (boolean)
     *                       - proximity  : value of distance between words (unsigned integer)
     **                      - fuzzy      : value of fuzzy(float, 0 ... 1)
     * @return $this
     */
    public function find($value, $field = '*', array $options = [])
    {
        $this->query = $this->addSubquery($this->query, [
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
     * Add subquery to boolean query.
     *
     * @param QueryBoolean $query
     * @param array $options
     * @return QueryBoolean
     */
    public function addSubquery(QueryBoolean $query, array $options)
    {
        list($value, $sign) = $this->buildRawLuceneQuery($options);
        $query->addSubquery(QueryParser::parse($value), $sign);

        $this->current_query_clauses[] = $value;
        return $query;
    }

    /**
     * Build raw Lucene query by given options.
     *
     * @param array $options - field      : field name
     *                       - value      : value to match
     *                       - phrase     : phrase match (boolean)
     *                       - required   : should match (boolean)
     *                       - prohibited : should not match (boolean)
     *                       - proximity  : value of distance between words (unsigned integer)
     **                      - fuzzy      : value of fuzzy(float, 0 ... 1)
     * @return array contains string query and sign
     */
    public function buildRawLuceneQuery($options)
    {
        $field = array_get($options, 'field');

        $value = trim($this->escapeSpecialChars(array_get($options, 'value')));

        if (empty($field) || '*' === $field) {
            $field = null;
        }

        if (array_get($options, 'phrase') || array_get($options, 'proximity')) {
            $value = '"' . $value . '"';
        } else {
            $value = $this->escapeSpecialOperators($value);
        }

        if (isset($options['proximity']) && false !== $options['proximity']) {
            if (is_integer($options['proximity']) && $options['proximity'] > 0) {
                $proximity = $options['proximity'];
                $value = $value . '~' . $proximity;
            }
        }

        if (isset($options['fuzzy']) && false !== $options['fuzzy']) {
            $fuzzy = '';
            if (is_numeric($options['fuzzy']) && $options['fuzzy'] >= 0 && $options['fuzzy'] <= 1) {
                $fuzzy = $options['fuzzy'];
            }

            $words = array();
            foreach (explode(' ', $value) as $word) {
                $words[] = $word . '~' . $fuzzy;
            }
            $value = implode(' ', $words);
        }

        if (is_array($field)) {
            $values = array();
            foreach ($field as $f) {
                $values[] = trim($f) . ':(' . $value . ')';
            }
            $value = implode(' OR ', $values);
        } elseif ($field) {
            $value = trim($field) . ':(' . $value . ')';
        }

        if (!empty($options['required'])) {
            $sign = true;
        } elseif (!empty($options['prohibited'])) {
            $sign = false;
        } else {
            $sign = null;
        }

        return [$value, $sign];
    }

    /**
     * Escape special characters for Lucene query.
     *
     * @param string $str
     *
     * @return string
     */
    private function escapeSpecialChars($str)
    {
        // List of all special chars.
        $special_chars = ['\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':'];


        // Escape all special characters.
        foreach ($special_chars as $ch) {
            $str = str_replace($ch, "\\{$ch}", $str);
        }

        return $str;
    }

    /**
     * Escape special operators for Lucene query.
     *
     * @param $str
     * @return mixed
     */
    private function escapeSpecialOperators($str)
    {
        // List of query operators.
        $query_operators = ['to', 'or', 'and', 'not'];

        // Add spaces to operators.
        $query_operators = array_map(function ($operator) {
            return " {$operator} ";
        }, $query_operators);

        // Remove other operators.
        $str = str_ireplace($query_operators, ' ', $str);

        return $str;
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

        foreach ($results as $model) {
            $this->search->delete($model);
        }
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
        $page = (int)Input::get('page', 1);
        $this->limit($perPage, ($page - 1) * $perPage);

        return App::make('paginator')->make($this->get(), $this->count(), $perPage);
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
     * Execute current query and return list of models.
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

        // Get hits.
        $hits = $this->executeQuery($this->query, $options);

        // Convert hits to models.
        return $this->search->config()->models($hits);
    }


    /**
     * Execute added callback functions.
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
     * @param string|AbstractQuery $query
     * @param array $options - limit  : max number of records to return
     *                       - offset : number of records to skip
     * @return array|QueryHit
     */
    public function executeQuery($query, array $options = [])
    {
        $hits = $this->search->index()->find($query);

        // Remember total number of results.
        $this->cached_query_totals[md5(serialize($query))] = count($hits);

        // Remember running query.
        self::$last_query = $query;
        self::$last_query_clauses = $this->current_query_clauses;

        // Limit results.
        if (isset($options['limit']) && isset($options['offset'])) {
            $hits = array_slice($hits, $options['offset'], $options['limit']);
        }

        return $hits;
    }
}
