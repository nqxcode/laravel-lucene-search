<?php namespace Nqxcode\LuceneSearch\Pagination;

/**
 * Class Factory
 * @package Nqxcode\LuceneSearch\Pagination
 */
class Factory extends \Illuminate\Pagination\Factory
{
    public function make(array $items, $total, $perPage = null)
    {
        $paginator = new Paginator($this, $items, $total, $perPage);

        return $paginator->setupPaginationContext();
    }
}
