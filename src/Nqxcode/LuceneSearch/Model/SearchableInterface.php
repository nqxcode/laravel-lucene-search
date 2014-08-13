<?php
namespace Nqxcode\LuceneSearch\Model;

use Illuminate\Database\Eloquent\Collection;

/**
 * Interface SearchableInterface
 * @package Nqxcode\LuceneSearch
 */
interface SearchableInterface
{
    /**
     * Is the model available for search indexing?
     *
     * @return boolean
     */
    public function isSearchable();

    /**
     * Get collection of searchable models.
     *
     * @return Collection|static[]
     */
    public function allSearchable();
}
