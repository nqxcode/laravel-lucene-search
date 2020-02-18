<?php namespace Nqxcode\LuceneSearch\Job;

use App\Models\CatalogProduct;

/**
 * Class MassUpdateSearchIndex
 */
class MassUpdateSearchIndex
{
    public function fire($job, array $jobData)
    {
        $modelClass = $jobData['modelClass'];
        $modelKeys = $jobData['modelKeys'];

        foreach ($modelKeys as $modelKey) {
            $model = $modelClass::find($modelKey);
            if (!is_null($model)) {
                app('search')->update($model);
            }
        }

        $job->delete();
    }
}
