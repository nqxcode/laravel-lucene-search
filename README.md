laravel-lucene-search
==============

[![Build Status](https://travis-ci.org/nqxcode/laravel-lucene-search.svg?branch=master)](https://travis-ci.org/nqxcode/laravel-lucene-search)

Laravel 4 package for full-text search over Eloquent models based on ZendSearch Lucene.

## Installation

Require this package in your composer.json and run composer update:

```json
{
	"require": {
        "nqxcode/laravel-lucene-search": "1.*"
	}
}
```

After updating composer, add the ServiceProvider to the providers array in `app/config/app.php`

```php
'providers' => [
	'Nqxcode\LuceneSearch\ServiceProvider',
],
```

If you want to use the facade to search, add this to your facades in `app/config/app.php`:

```php
'aliases' => [
	'Search' => 'Nqxcode\LuceneSearch\Facade',
],
```
## Configuration 
Publish the config file into your project by running:

```bash
php artisan config:publish nqxcode/laravel-lucene-search
```

In published config file add descriptions for models which need to be indexed, for example:

```php
'index' => [
	
	// ...

	'namespace\FirstModel' => [
		'fields' => [
			'name', 'full_description', // Fields for indexing.
		]
	],
	
	'namespace\SecondModel' => [
		'fields' => [
			'name', 'short_description', // Fields for indexing.
		]
	],
	
	// ...
```
By default the following filters are used by search:
* Stemming filter for english/russian words,
* Stopword filters for english/russian words.

This filters can be deleted or replaced with others.
```php

    'analyzer' => [
        'filters' => [
        	// Default stemming filter.
        	'Nqxcode\Stemming\TokenFilterEnRu',
        ],
        
	// List of pathes to files with stopwords. 
	'stopwords' => Nqxcode\LuceneSearch\Analyzer\Stopwords\Files::get(),
    ],
    
```

## Usage
### Artisan commands
#### Build/Rebuild search index
For building of search index run:

```bash
php artisan search:rebuild
```
#### Clear search index
For clearing of search index run:

```bash
php artisan search:clear
```

### Register events for models

For register models events (save/update/delete) `use SearchTrait` and call `registerEventsForSearch` method of trait in `boot` method of model:

```php
    
    use SearchTrait;
    
    // ...
    
    public static function boot()
    {
    	parent::boot();
        self::registerEventsForSearch();
    }

```

### Query building
Build query in several ways:

#### Using constructor:

```php

$query = Search::find('clock');
// or 
$query = Search::where('name', 'clock');
// or
$query = Search::find('clock')->where('short_description', 'analog');

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
#### Get limit results with offset

```php
$models = $query->limit(5, 10)->get(); // Limit = 5 and offset = 10
```
#### Paginate the found models

```php
$paginator = $query->paginate(50);
```

##
## License
Search engine licenced under the MIT license.
