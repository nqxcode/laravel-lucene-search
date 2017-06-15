<?php namespace tests\models;

use Illuminate\Database\Eloquent\Model;
use Nqxcode\LuceneSearch\Model\SearchTrait;

/**
 * Class Tool
 * @property string $name
 * @property string $description
 * @package tests\models
 */
class Tool extends Model
{
    use SearchTrait;
}
