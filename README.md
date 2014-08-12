laravel-lucene-search
==============

[![Build Status](https://travis-ci.org/nqxcode/laravel-lucene-search.svg?branch=master)](https://travis-ci.org/nqxcode/laravel-lucene-search)

Laravel 4 package for full-text search over Eloquent models based on ZF2 Lucene.

## Installation

Require this package in your composer.json and run composer update (or run `composer require nqxcode/laravel-lucene-search:1.x` directly):

    "nqxcode/laravel-lucene-search": "1.*"

After updating composer, add the ServiceProvider to the providers array in app/config/app.php

    'Nqxcode\LuceneSearch\ServiceProvider',

If you want to use the facade to search, add this to your facades in app.php:

    'Search' => 'Nqxcode\LuceneSearch\Facade',
