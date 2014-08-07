<?php
namespace Nqxcode\LaravelSearch\Query\Builder;

use Nqxcode\LaravelSearch\Query\Filter;
use Nqxcode\LaravelSearch\Query\LuceneQueryBuilder;
use Nqxcode\LaravelSearch\Query\Runner;
use ZendSearch\Lucene\Search\Query\Boolean as QueryBoolean;

class Boolean extends AbstractBuilder
{
    public function __construct(
        Runner $runner,
        Filter $filter,
        LuceneQueryBuilder $queryBuilder,
        QueryBoolean $query
    ) {
        parent::__construct($runner, $filter, $queryBuilder);
        $this->query = $query;
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
     *                       - phrase     : phrase match (boolean, false by default)
     *                       - proximity  : value of distance between words (unsigned integer)
     *                       - fuzzy      : value of fuzzy(float, 0 ... 1)
     * @return $this
     */
    public function find($value, $field = '*', array $options = [])
    {
        $options['field'] = $field;
        $options['value'] = $value;
        $options = $this->defaultOptions($options);

        $this->query = $this->addSubquery($this->query, $options);

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
        $options['field'] = $field;
        $options['value'] = $value;
        $options = $this->defaultOptions($options);

        $options['phrase'] = true;

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
            'phrase' => array_get($options, 'phrase', false),
            'fuzzy' => array_get($options, 'fuzzy', null),
            'proximity' => array_get($options, 'proximity', null),
        ];
    }
} 