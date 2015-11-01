<?php namespace Nqxcode\LuceneSearch\Support;

use Illuminate\Database\Eloquent\Model;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
    protected $reloaded = false;

    /**
     * Reload each item in collection.
     *
     * @return $this|static
     */
    public function reload()
    {
        if (!$this->reloaded) {
            $this->reloaded = true;

            $items = array_map(
                function (Model $sourceModel) {
                    $primaryKey = $sourceModel->getKeyName();
                    $primaryValue = $sourceModel->{$primaryKey};

                    $query = $sourceModel->query();
                    $query->where($primaryKey, $primaryValue);

                    $targetModel = $query->first();

                    return $targetModel;
                },
                $this->items
            );

            $items = array_filter(
                $items,
                function ($item) {
                    return !is_null($item);
                }
            );

            $this->items = $items;
        }

        return $this;
    }
}