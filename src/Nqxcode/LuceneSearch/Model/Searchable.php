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

    /**
     * Set score of the hit.
     *
     * @param $score
     * @return float
     */
    public function setScore($score);

    /**
     * Get score of the hit.
     *
     * @return float
     */
    public function getScore();
}
