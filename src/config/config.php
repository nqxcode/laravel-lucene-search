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
     | Each description should contains class of model and fields available
     | for search indexing.
     |
     |
     | For example, model's description can be like this:
     |
     |      'namespace\ModelClass' => [
     |          'fields' => [
     |              'name', 'description',
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
     |
    */
    'token_filters' => [],

    /*
     |--------------------------------------------------------------------------
     | Stop words files
     |--------------------------------------------------------------------------
     |
     | The list of pathes to files with stopwords.
     |
    */
    'stopwords_files' => [],
);
