<?php namespace tests\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Nqxcode\LuceneSearch\Model\SearchableInterface;
use Nqxcode\LuceneSearch\Model\SearchTrait;

/**
 * Class Product
 * @property string $name
 * @property string $description
 * @property boolean $publish
 * @method Builder wherePublish(boolean $publish)
 * @package tests\models
 */
class Product extends Model implements SearchableInterface
{
    use SearchTrait;

    /**
     * @inheritdoc
     */
    public static function searchableIds()
    {
        static $ids;
        if (is_null($ids)) {
            $ids = self::wherePublish(true)->lists('id');
        }

        return $ids;
    }

    public function getCustomOptionalAttributesAttribute()
    {
        return [
            'custom_text' => 'some custom text',
            'boosted_name' => ['boost' => 0.9, 'value' => $this->name],
        ];
    }

    public function getCustomBoostAttribute()
    {
        return $this->attributes['availability'] ? 1 : 0.1;
    }
}
