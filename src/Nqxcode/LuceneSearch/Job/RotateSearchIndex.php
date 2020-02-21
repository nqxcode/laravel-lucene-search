<?php namespace Nqxcode\LuceneSearch\Job;

use File;
use Nqxcode\LuceneSearch\Support\SearchIndexRotator;

/**
 * Class RotateSearchIndex
 * @package Nqxcode\LuceneSearch\Job
 */
class RotateSearchIndex
{
    public function fire($job, array $jobData)
    {
        app('search.index.rotator')->rotate();

        $job->delete();
    }
}
