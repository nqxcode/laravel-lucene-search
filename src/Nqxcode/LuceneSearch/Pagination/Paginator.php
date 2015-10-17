<?php namespace Nqxcode\LuceneSearch\Pagination;

use Nqxcode\LuceneSearch\Support\Collection;

/**
 * Class Paginator
 * @package LuceneSearch\Pagination
 */
class Paginator extends \Illuminate\Pagination\Paginator
{
    public function getItems()
    {
        return Collection::make($this->items)->unlazy()->all();
    }
}