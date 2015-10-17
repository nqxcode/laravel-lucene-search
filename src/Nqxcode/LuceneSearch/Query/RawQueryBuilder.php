<?php namespace Nqxcode\LuceneSearch\Query;

use ZendSearch\Lucene\Search\QueryParser;

/**
 * Class RawQueryBuilder
 * @package Nqxcode\LuceneSearch\Query
 */
class RawQueryBuilder
{
    /**
     * Build raw lucene query by given options.
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
    public function build($options)
    {
        $field = array_get($options, 'field');

        $value = trim($this->escapeSpecialChars(array_get($options, 'value')));

        if (empty($field) || '*' === $field) {
            $field = null;
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

        if (array_get($options, 'phrase') || array_get($options, 'proximity')) {
            $value = '"' . $value . '"';
        } else {
            $value = $this->removeSpecialOperators($value);
        }

        if (isset($options['proximity']) && false !== $options['proximity']) {
            if (is_integer($options['proximity']) && $options['proximity'] > 0) {
                $proximity = $options['proximity'];
                $value = $value . '~' . $proximity;
            }
        }

        $tmpValue = $value;

        if (is_array($field)) {
            $values = array();
            foreach ($field as $f) {
                $values[] = trim($f) . ':(' . $value . ')';
            }
            $value = implode(' OR ', $values);
        } elseif ($field) {
            $value = trim($field) . ':(' . $value . ')';
        }

        $sign = null;
        if (!empty($options['required'])) {
            $sign = true;
        } elseif (!empty($options['prohibited'])) {
            $sign = false;
        }

        $value = "({$value}) AND NOT primary_key:({$tmpValue}) AND NOT class_uid:({$tmpValue})";

        return [$value, $sign];
    }

    /**
     * Get query object by given query string.
     *
     * @param string $strQuery
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    public function parse($strQuery)
    {
        return QueryParser::parse($strQuery);
    }

    /**
     * Escape special characters for raw query.
     *
     * @param string $str
     *
     * @return string
     */
    protected function escapeSpecialChars($str)
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
     * Remove special operators for raw query.
     *
     * @param $str
     * @return mixed
     */
    protected function removeSpecialOperators($str)
    {
        // List of query operators.
        $query_operators = ['to', 'or', 'and', 'not'];

        // Add spaces to operators.
        $query_operators = array_map(
            function ($operator) {
                return " {$operator} ";
            },
            $query_operators
        );

        // Remove other operators.
        $str = str_ireplace($query_operators, ' ', $str);

        return $str;
    }
}
