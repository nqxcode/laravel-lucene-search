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
     | Models
     |--------------------------------------------------------------------------
     |
     | The list of the descriptions for models.
     |
     | Each description must contains class of model and fields available
     | for search indexing.
     |
     |
     | For example, model's description can be like this:
     |
     |      'namespace\ModelClass' => [
     |          'fields' => [
     |              'name', 'description', // Fields for indexing.
     |          ]
     |      ]
     |
     */
    'models' => [],

    /*
     |--------------------------------------------------------------------------
     | ZendSearch token filters
     |--------------------------------------------------------------------------
     |
     | The list of classes implementing the TokenFilterInterface interface.
     | Stemming token filter for english/russian words is enabled by default.
     | To disable it remove class 'Nqxcode\Stemming\TokenFilterEnRu' from
     | token filters.
     |
    */
    'token_filters' => ['Nqxcode\Stemming\TokenFilterEnRu'],

    /*
     |--------------------------------------------------------------------------
     | Stop words files
     |--------------------------------------------------------------------------
     |
     | The list of path1s to files with stopwords.
     |
    */
    'stopwords_files' => [],
);
