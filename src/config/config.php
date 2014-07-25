<?php

return array(
    /*
     |--------------------------------------------------------------------------
     | Path to Lucene search index.
     |--------------------------------------------------------------------------
     |
     | TODO Write description!
     |
     |
     */
    'index_path' => storage_path() . '/laravel-search/index',

    /*
     |--------------------------------------------------------------------------
     | List of models descriptions.
     |
     | Each description should contain class of model and fields available
     | for search indexing.
     |
     |--------------------------------------------------------------------------
     |
     | For example, one of model's description can be such as this:
     |
     |      'namespace\DummyModel' => [
     |          'fields' => [
     |              'name', 'description',
     |          ]
     |      ]
     |
     */
    'models' => [],


    'filters' => [],

    'stop_words_paths' => [],
);
