<?php namespace tests\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Nqxcode\LuceneSearch\Model\Searchable;
use Nqxcode\LuceneSearch\Model\Search;

/**
 * Class Product
 * @property string $name
 * @property string $description
 * @property boolean $publish
 * @method Builder wherePublish
 * @package tests\models
 */
class Product extends Model implements Searchable
{
    use Search;
    /**
     * @inheritdoc
     */
    public function isSearchable()
    {
        return $this->publish;
    }

    public static function boot()
    {
        self::registerEventsForSearchIndexUpdate();
    }
}
