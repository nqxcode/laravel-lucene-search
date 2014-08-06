<?php
namespace Nqxcode\LaravelSearch\Query;

use ZendSearch\Lucene\Search\Query\Boolean as QueryBoolean;
use ZendSearch\Lucene\Search\QueryParser;

class Constructor extends Builder
{
    /** @var \Nqxcode\LaravelSearch\Query\Lucene */
    private $rawQueryBuilder;

    public function __construct(Runner $runner, QueryBoolean $query, Lucene $rawQueryBuilder)
    {
        parent::__construct($runner);
        $this->query = $query;
        $this->rawQueryBuilder = $rawQueryBuilder;
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
        list($value, $sign) = $this->rawQueryBuilder->buildRawQuery($options);
        $query->addSubquery(QueryParser::parse($value), $sign);
        return $query;
    }

    /**
     * Add a basic search clause to the query.
     *
     * @param $value
     * @param $field
     * @param array $options - required   : should match (boolean, true by default)
     *                       - prohibited : should not match (boolean, false by default)
     *                       - phrase     : phrase match (boolean, false by default)
     *                       - proximity  : value of distance between words (unsigned integer)
     *                       - fuzzy      : value of fuzzy(float, 0 ... 1)
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
     * Add where clause to the query for phrase search.
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
} 