<?php namespace Nqxcode\LuceneSearch\Support;

use Illuminate\Database\Eloquent\Builder;
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
            $targetPrimaryKeys = $targets->lists($primaryKeyName);
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

    /**
     * Key an associative array by a field or using a callback.
     *
     * @param  callable|string $keyBy
     * @return static
     */
    public function keyBy($keyBy)
    {
        $keyBy = $this->valueRetriever($keyBy);
        $results = [];
        foreach ($this->items as $key => $item) {
            $resolvedKey = $keyBy($item, $key);
            if (is_object($resolvedKey)) {
                $resolvedKey = (string)$resolvedKey;
            }
            $results[$resolvedKey] = $item;
        }
        return new static($results);
    }

    /**
     * Group an associative array by a field or using a callback.
     *
     * @param  callable|string $groupBy
     * @param  bool $preserveKeys
     * @return static
     */
    public function groupBy($groupBy, $preserveKeys = false)
    {
        $groupBy = $this->valueRetriever($groupBy);
        $results = [];
        foreach ($this->items as $key => $value) {
            $groupKeys = $groupBy($value, $key);
            if (!is_array($groupKeys)) {
                $groupKeys = [$groupKeys];
            }
            foreach ($groupKeys as $groupKey) {
                $groupKey = is_bool($groupKey) ? (int)$groupKey : $groupKey;
                if (!array_key_exists($groupKey, $results)) {
                    $results[$groupKey] = new static;
                }
                $results[$groupKey]->offsetSet($preserveKeys ? $key : null, $value);
            }
        }
        return new static($results);
    }

    /**
     * Get a value retrieving callback.
     *
     * @param  string $value
     * @return callable
     */
    protected function valueRetriever($value)
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }
        return function ($item) use ($value) {
            return data_get($item, $value);
        };
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param  mixed $value
     * @return bool
     */
    protected function useAsCallable($value)
    {
        return !is_string($value) && is_callable($value);
    }


    /**
     * Filter items by the given key value pair.
     *
     * @param  string $key
     * @param  mixed $operator
     * @param  mixed $value
     * @return static
     */
    public function where($key, $operator, $value = null)
    {
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }
        return $this->filter($this->operatorForWhere($key, $operator, $value));
    }

    /**
     * Get an operator checker callback.
     *
     * @param  string  $key
     * @param  string  $operator
     * @param  mixed  $value
     * @return \Closure
     */
    protected function operatorForWhere($key, $operator, $value)
    {
        return function ($item) use ($key, $operator, $value) {
            $retrieved = data_get($item, $key);
            switch ($operator) {
                default:
                case '=':
                case '==':  return $retrieved == $value;
                case '!=':
                case '<>':  return $retrieved != $value;
                case '<':   return $retrieved < $value;
                case '>':   return $retrieved > $value;
                case '<=':  return $retrieved <= $value;
                case '>=':  return $retrieved >= $value;
                case '===': return $retrieved === $value;
                case '!==': return $retrieved !== $value;
            }
        };
    }
}
