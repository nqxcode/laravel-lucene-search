<?php
namespace Nqxcode\LaravelSearch;

use Illuminate\Database\Eloquent\Collection;

/**
 * Interface Searchable
 * @package Nqxcode\LaravelSearch
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
     * Get collection of searchable models.
     *
     * @return Collection|static[]
     */
    public function allSearchable();
}
