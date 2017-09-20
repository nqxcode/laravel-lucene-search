<?php namespace Nqxcode\LuceneSearch\Model;

/**
 * Interface SearchableInterface
 * @package Nqxcode\LuceneSearch\Model
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
