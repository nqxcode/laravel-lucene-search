<?php namespace Nqxcode\LuceneSearch;

use Search;

trait SearchTrait
{
    /**
     * Update model in search index.
     */
    public function updateSearchIndex()
    {
        Search::update($this);
    }

    /**
     * Delete model from search index.
     */
    public function deleteSearchIndex()
    {
        Search::delete($this);
    }
}
