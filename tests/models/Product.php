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
 * @method Builder wherePublish
 * @package tests\models
 */
class Product extends Model implements SearchableInterface
{
    use SearchTrait;
    /**
     * @inheritdoc
     */
    public function isSearchable()
    {
        return $this->publish;
    }

    /**
     * @inheritdoc
     */
    public function allSearchable()
    {
        return $this->wherePublish(1)->get();
    }

    public static function boot()
    {
        self::registerSearchIndexUpdateEvents();
    }
}
