<?php
namespace Nqxcode\LuceneSearch\Model;

/**
 * Interface Searchable
 * @package Nqxcode\LuceneSearch
 */
interface Searchable
{
    /**
     * Is the model available for search indexing?
     *
     * @return boolean
     */
    public function isSearchable();
}
