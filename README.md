# Laravel JSON search

This package enables ``search`` method on Eloquent models for 
Laravel 7 to enable detailed DB search through URL query string. 

It functions out-of-the-box automatically for all Eloquent models 
within the project. No additional setup is needed.

PHP min version: 7.4.

## Installation

Install the package through composer. It is automatically registered
as a Laravel service provider, so no additional actions are required.

``composer require asseco-voice/laravel-json-search``

## Quick usage

Create a GET search endpoint

```
Route::get('search', 'ExampleController@search');
```

Call the method within the controller and forward a full `Illuminate\Http\Request` object to the search method.

```
public function search(Request $request)
{
    return SomeModel::search($request)->get();
}
```
 
Call the endpoint providing the query string:

```
www.example.com/search?search=(first_name=foo;bar;!baz\last_name=test)
```
    
This will perform a ``SELECT * FROM some_table WHERE first_name IN ('foo, 'bar') 
AND first_name not in ('baz') or last_name in ('test')``.

## In depth

For detailed engine usage and logic, refer to 
[this readme](https://github.com/asseco-voice/laravel-json-query-builder).

## Config 

Aside from standard query string search, it is possible to provide additional 
package configuration.

Publish the configuration by running 
`php artisan vendor:publish --provider="Voice\JsonSearch\SearchServiceProvider"`.

All the keys within the configuration file have a detailed explanation above each key.
