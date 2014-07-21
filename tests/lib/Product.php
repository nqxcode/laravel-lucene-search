<?php namespace tests\lib;

use \Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function isSearchable()
    {
        return true;
    }
}
