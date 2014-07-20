<?php namespace tests\lib;

class Product extends \Illuminate\Database\Eloquent\Model
{
    public function isSearchable()
    {
        return true;
    }
}
