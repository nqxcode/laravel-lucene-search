<?php namespace Nqxcode\LuceneSearch\Support;

use Illuminate\Database\Eloquent\Model;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
    /* @var bool can it be unlazy? */
    protected $lazy = false;

    /**
     * @inheritdoc
     */
    public function __construct(array $items = array())
    {
        parent::__construct($items);
        $this->lazy = true;
    }

    /**
     * @inheritdoc
     */
    public static function make($items)
    {
        /** @var self $collection */
        $collection = parent::make($items);
        $collection->lazy = true;

        return $collection;
    }

    /**
     * Make collection not lazy.
     *
     * @return $this|static
     */
    public function unlazy()
    {
        if ($this->lazy) {
            $this->lazy = false; // it may be unlazy only once

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