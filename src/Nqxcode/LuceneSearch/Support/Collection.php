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
        /** @var Model $source */
        foreach ($this->items as $i => $source) {
            if (!$source->exists) {
                /** @var Model $target */
                $target = $source->find($source->{$source->getKeyName()});
                if (!is_null($target)) {
                    $source->setRawAttributes($target->getAttributes(), true);
                    $source->exists = true;
                } else {
                    unset($this->items[$i]);
                }
            }
        }
        return $this;
    }
}