<?php namespace Nqxcode\LuceneSearch\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Collection
 * @package Nqxcode\LuceneSearch\Support
 */
class Collection extends \Illuminate\Database\Eloquent\Collection
{
    /**
     * Reload each nonexistent item in collection.
     *
     * @return $this|static
     */
    public function reload()
    {
        $groups = $this->groupBy(
            function (Model $model) {
                return $model->getTable();
            },
            true
        );

        /** @var Collection $groups */
        foreach ($groups as $group) {
            /** @var Model|Builder $targetRepo */
            $targetRepo = $group->first();

            /** @var Collection|Model[] $targets */
            $targets = $group->where('exists', false);

            $primaryKeyName = $targetRepo->getKeyName();
            $targetPrimaryKeys = $targets->pluck($primaryKeyName)->all();
            $sourceDictionary = $targetRepo->find($targetPrimaryKeys)->keyBy($primaryKeyName);

            foreach ($targets as $key => $target) {
                $primaryKey = $target->{$primaryKeyName};

                if ($sourceDictionary->has($primaryKey)) {
                    /** @var Model $source */
                    $source = $sourceDictionary->get($primaryKey);
                    $target->setRawAttributes($source->getAttributes(), true);
                    $target->exists = true;

                } else {
                    $this->forget($key);
                }
            }
        }

        $this->items = array_values($this->items);

        return $this;
    }
}
