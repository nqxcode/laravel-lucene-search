<?php

use Illuminate\Support\Facades\Config;

return array(
    /*
     |--------------------------------------------------------------------------
     | Index settings
     |--------------------------------------------------------------------------
     |
     | TODO Write description!
     |
     |
     */
    'index' => [
        'path' => storage_path() . '/laravel-search/index',
    ],

    'models' => [
        'lib\\Product' => [
            'fields' => [
                'name' => [],
                'description' => [],
            ]
        ]
    ]
);
