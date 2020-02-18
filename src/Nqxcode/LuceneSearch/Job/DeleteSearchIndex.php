<?php namespace Nqxcode\LuceneSearch\Job;

/**
 * Class DeleteSearchIndex
 */
class DeleteSearchIndex
{
    public function fire($job, array $jobData)
    {
        $model = $jobData['modelClass']::find($jobData['modelKey']);
        if (!is_null($model)) {
            app('search')->delete($model);
        }

        $job->delete();
    }
}
