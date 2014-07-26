<?php

return array(
    /*
     |--------------------------------------------------------------------------
     | Path to Lucene search index.
     |--------------------------------------------------------------------------
     |
     */
    'index_path' => storage_path() . '/laravel-search/index',

    /*
     |--------------------------------------------------------------------------
     | List of models descriptions.
     |--------------------------------------------------------------------------
     |
     | Each description should contain class of model and fields available
     | for search indexing.
     |
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

    /*
     |----------     |----------------------------------------------------------------
     | List of filter for search index analyzer.
     |--------------------------------------------------------------------------
     |
     | Each filter is a class implements interface TokenFilterInterface
     | from ZendSearch library.
     |
     |
    */
    'token_filters' => [],

    /*
     |--------------------------------------------------------------------------
     | List of files with stop words.
     |--------------------------------------------------------------------------
     |
    */
    'stop_words_paths' => [],
);
