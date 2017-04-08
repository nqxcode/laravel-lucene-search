<?php namespace Nqxcode\LuceneSearch\Support;

use Illuminate\Database\Eloquent\Model;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
    /**
     * Reload each nonexistent item in collection.
     *
     * @return $this|static
     */
    public function reload()
    {
        $groups = $this->groupBy('table', true);

        /** @var Collection $group */
        foreach ($groups as $tableName => $group) {
            $keys = [];
            $newItems = $group->where('exists', false);
            /** @var Model $source */
            foreach ($newItems as $source) {
                $keys[] = $source->{$source->getKeyName()};
            }
            $model = $group->first();
            $keyName = $model->getKeyName();
            $targets = $model->find($keys)->keyBy($keyName);

            foreach ($newItems as $key => $source) {
                if ($targets->has($source->{$source->getKeyName()})) {
                    $source->setRawAttributes($targets->get($source->{$source->getKeyName()})->getAttributes(), true);
                    $source->exists = true;
                } else {
                    $this->forget($key);
                }
            }
        }

        return $this;
    }
}
