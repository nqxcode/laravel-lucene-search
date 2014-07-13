<?php namespace Nqxcode\LaravelSearch;

trait SearchTrait
{
    /**
     * Update model in search index.
     */
    public function updateIndex()
    {
        \Search::update($this);
    }

    /**
     * Delete model from search index.
     */
    public function deleteIndex()
    {
        \Search::delete($this);
    }
}
