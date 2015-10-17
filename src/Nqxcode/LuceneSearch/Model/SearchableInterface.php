<?php namespace Nqxcode\LuceneSearch\Model;

/**
 * Interface Searchable
 * @package Nqxcode\LuceneSearch
 */
interface SearchableInterface
{
    /**
     * Get id list for all searchable models.
     *
     * @return integer[]
     */
    public static function searchableIds();
}
