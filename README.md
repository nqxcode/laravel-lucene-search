Laravel 5.2 Lucene search
==============

[![Latest Stable Version](https://poser.pugx.org/nqxcode/laravel-lucene-search/v/stable.png)](https://packagist.org/packages/nqxcode/laravel-lucene-search)
[![Latest Unstable Version](https://poser.pugx.org/nqxcode/laravel-lucene-search/v/unstable.png)](https://packagist.org/packages/nqxcode/laravel-lucene-search)
[![License](https://poser.pugx.org/nqxcode/laravel-lucene-search/license.png)](https://packagist.org/packages/nqxcode/laravel-lucene-search)
[![Build Status](https://travis-ci.org/nqxcode/laravel-lucene-search.svg?branch=master)](https://travis-ci.org/nqxcode/laravel-lucene-search)
[![Coverage Status](https://img.shields.io/coveralls/nqxcode/laravel-lucene-search/master.svg?style=flat)](https://coveralls.io/r/nqxcode/laravel-lucene-search?branch=master)

Laravel 5.2 package for full-text search over Eloquent models based on ZendSearch Lucene.

## Installation

Require this package in your composer.json and run composer update:

```json
{
	"require": {
        "nqxcode/laravel-lucene-search": "2.1.*"
	}
}
```

After updating composer, add the ServiceProvider to the providers array in `app/config/app.php`

```php
'providers' => [
	Nqxcode\LuceneSearch\ServiceProvider::class,
],
```

If you want to use the facade to search, add this to your facades in `app/config/app.php`:

```php
'aliases' => [
	'Search' => Nqxcode\LuceneSearch\Facade::class,
],
```
## Configuration 

Publish the config file into your project by running:

```bash
php artisan vendor:publish --provider="Nqxcode\LuceneSearch\ServiceProvider"
```
###Basic
In published config file add descriptions for models which need to be indexed, for example:

```php
'index' => [
	
	// ...

	namespace\FirstModel::class => [
		'fields' => [
			'name', 'full_description', // fields for indexing
		]
	],
	
	namespace\SecondModel::class => [
		'fields' => [
			'name', 'short_description', // fields for indexing
		]
	],
	
	// ...
	
],

```
###Indexing of dynamic fields
You can also index values of **optional fields** (dynamic fields). For enable indexing for optional fields:

- In config for each necessary model add following option:
```php
        'optional_attributes' => true
        
        // or
        
        'optional_attributes' => [
                'accessor' => 'custom_name' // with specifying of accessor name
        ]
```
- In model add **special accessor**, that returns list of `field-name => field-value`.
By default `getOptionalAttributesAttribute` accessor will be used.
In case accessor name specified in config `getCustomNameAttribute` accessor will be used.

Example:

In config file:

```php
        namespace\FirstModel::class => [
                'fields' => [
                    'name', 'full_description', // fixed fields for indexing
                ],

                'optional_attributes' => true //  enable indexing for dynamic fields
        ],
```

In model add following accessor:

```php
        public function getOptionalAttributesAttribute()
        {
                return [
                        'optional_attribute1' => 'value1',
                        'optional_attribute2' => 'value2',
                ];
        }
```
### Score Boosting
See details on [Apache Lucene - Scoring](https://lucene.apache.org/core/3_5_0/scoring.html#Score%20Boosting).

####Model level boosting
This is **Document level boosting** in terminology of Apache Lucene. By default all models have **boost** value equal to **1**. For change of this behavior customize boost for necessary models as in the following examples.

- In config for each necessary model add following option:
```php
        'boost' => true
        
        // or
        
        'boost' => [
                'accessor' => 'custom_name' // with specifying of accessor name
        ]
```
In model add following accessor:

```php
        public function getBoostAttribute()
        {
                return 0.5; // customize boost value for model
        }
```
- In model add **special accessor**, that returns boost value.
By default `getBoostAttribute` accessor will be used.
In case accessor name specified in config `getCustomNameAttribute` accessor will be used.

Example:

In config file:

```php
        namespace\FirstModel::class => [
                'fields' => [
                    'name', 'full_description',
                ],
                
                'boost' => true // enable boosting for model
        ],
```

In model add following accessor:

```php
        public function getBoostAttribute()
        {
                return 0.5; // customize boost value for model
        }
```

####Model's field level boosting
This is **Document's Field level boosting** in terminology of Apache Lucene.
By default **boost** is set in **1** for each field. For change of this behavior set boost for necessary fields as in the following examples.

In config file:

```php
        namespace\FirstModel::class => [
                'fields' => [
                    'name', // field with default boost
                    'full_description' => ['boost' => 0.2], // customize boost value
                ],
        ],
```

Or/and in model accessor:
```php
        public function getOptionalAttributesAttribute()
        {
                return [
                        'optional_attribute1' => 'value1', // field with default boost
                        'optional_attribute2' => ['boost' => 0.5, 'value' => 'value2'], // customize boost value
                ];
        }
```

###Stemming and stopwords
By default the following filters are used in search:
- Stemming filter for **english/russian** words (for reducing words to their root form),
- Stopword filters for **english/russian** words (for exclude some words from search index).

This filters can be deleted or replaced with others.
```php

'analyzer' => [
    'filters' => [
    	// Default stemming filter.
    	Nqxcode\Stemming\TokenFilterEnRu::class,
    ],
        
    // List of paths to files with stopwords. 
    'stopwords' => Nqxcode\LuceneSearch\Analyzer\Stopwords\Files::get(),
],
    
```

## Usage
### Artisan commands
#### Initialize or rebuild search index
For building of search index run:

```bash
php artisan search:rebuild --verbose
```
#### Clear search index
For clearing of search index run:

```bash
php artisan search:clear
```
#### Filtering of models in search results 
For filtering of models in search results each model's class can implements `SearchableInterface`.
For example:

```php

use Illuminate\Database\Eloquent\Model;
use Nqxcode\LuceneSearch\Model\SearchableInterface;

class Dummy extends Model implements SearchableInterface
{
        // ...

        /**
         * Get id list for all searchable models.
         */
        public static function searchableIds()
        {
            return self::wherePublish(true)->lists('id');
        }

        // ...
}

```

### Partial updating of search index
For register of necessary events (save/update/delete) `use Nqxcode\LuceneSearch\Model\SearchTrait` in target model:

```php

    use Illuminate\Database\Eloquent\Model;
    use Nqxcode\LuceneSearch\Model\SearchableInterface;
    use Nqxcode\LuceneSearch\Model\SearchTrait;

    class Dummy extends Model implements SearchableInterface
    {
        use SearchTrait;
    
        // ...
    }

```

### Query building
Build query in several ways:

#### Using constructor:

By default, queries which will execute search in the **phrase entirely** are created.

##### Simple queries
```php
$query = Search::query('clock'); // search by all fields.
// or 
$query = Search::where('name', 'clock'); // search by 'name' field.
// or
$query = Search::query('clock')              // search by all fields with
	->where('short_description', 'analog'); // filter by 'short_description' field. 
```
##### Advanced queries

For `query` and `where` methods it is possible to set the following options:
- **phrase**     - phrase match (boolean, true by default)
- **proximity**  - value of distance between words (unsigned integer)
- **fuzzy**      - value of fuzzy (float, 0 ... 1)
- **required**   - should match (boolean, true by default)
- **prohibited** - should not match (boolean, false by default)

###### Examples:

Find all models in which any field contains phrase like 'composite one two phrase':
```php 
$query = Search::query('composite phrase', '*', ['proximity' => 2]); 
```
Search by each word in query:
```php 
$query = Search::query('composite phrase', '*', ['phrase' => false]); 
```

#### Using Lucene raw queries:
```php
$query = Search::rawQuery('short_description:"analog"');
// or
$rawQuery = QueryParser::parse('short_description:"analog"');
$query = Search::rawQuery($rawQuery);
```
### Getting of results

For built query are available following actions:

#### Get all found models

```php
$models = $query->get();
```

#### Get count of results
```php
$count = $query->count();
```

#### Get limit results with offset

```php
$models = $query->limit(5, 10)->get(); // Limit = 5 and offset = 10
```
#### Paginate the found models

```php
$paginator = $query->paginate(50);
```
### Highlighting of matches

Highlighting of matches is available for any html fragment encoded in **utf-8** and is executed only for the last executed request.

```php
Search::find('nearly all words must be highlighted')->get();
$highlighted = Search::highlight('all words');

// highlighted html: 
// '<span class="highlight">all</span> <span class="highlight">words</span>'
```
##
## License
Package licenced under the MIT license.
