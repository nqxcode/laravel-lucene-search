<?php namespace Nqxcode\LuceneSearch\Job;

use App\Models\CatalogProduct;

/**
 * Class UpdateSearchIndex
 */
class UpdateSearchIndex
{
    public function fire($job, array $jobData)
    {
        $model = $jobData['modelClass']::find($jobData['modelKey']);
        if (!is_null($model)) {
            app('search')->update($model);
        }

        $job->delete();
    }
}
