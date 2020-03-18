<?php namespace Nqxcode\LuceneSearch\Job;

use File;
use Nqxcode\LuceneSearch\Search;
use Nqxcode\LuceneSearch\Support\SearchIndexRotator;

/**
 * Class RotateSearchIndex
 * @package Nqxcode\LuceneSearch\Job
 */
class RotateSearchIndex
{
    /**
     * @var Search
     */
    private $search;

    /**
     * @var SearchIndexRotator
     */
    private $searchIndexRotator;

    public function fire($job, array $jobData)
    {
        $this->search = app('search');
        $this->searchIndexRotator = app('search.index.rotator');

        $this->search->destroyConnection();
        $this->searchIndexRotator->rotate();

        $job->delete();
    }
}
