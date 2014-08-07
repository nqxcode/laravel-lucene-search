<?php
namespace Nqxcode\LaravelSearch\Query\Builder;

use Nqxcode\LaravelSearch\Query\LuceneQueryBuilder;
use Nqxcode\LaravelSearch\Query\Runner;
use ZendSearch\Lucene\Search\Query\Boolean as QueryBoolean;
use ZendSearch\Lucene\Search\QueryParser;

class Boolean extends AbstractBuilder
{
    /** @var \Nqxcode\LaravelSearch\Query\LuceneQueryBuilder */
    private $queryBuilder;

    /**
     * @param Runner $runner
     * @param QueryBoolean $query
     * @param LuceneQueryBuilder $queryBuilder
     */
    public function __construct(Runner $runner, QueryBoolean $query, LuceneQueryBuilder $queryBuilder)
    {
        parent::__construct($runner);
        $this->query = $query;
        $this->queryBuilder = $queryBuilder;
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