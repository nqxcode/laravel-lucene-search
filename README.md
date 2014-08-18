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

	// ...

	'Nqxcode\LuceneSearch\ServiceProvider',
],
```

If you want to use the facade to search, add this to your facades in `app/config/app.php`:

```php
'aliases' => [

	// ...

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
## License
Search engine licenced under the MIT license.
