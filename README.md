<p align="center"><a href="https://see.asseco.com" target="_blank"><img src="https://github.com/asseco-voice/art/blob/main/evil_logo.png" width="500"></a></p>

# Laravel JSON search

This package exposes a ``jsonSearch`` method on Laravel Eloquent models
providing a detailed DB search with JSON as input parameter. 

It functions out-of-the-box automatically for all Eloquent models 
within the project. No additional setup is needed.

## Installation

Install the package through composer. It is automatically registered
as a Laravel service provider, so no additional actions are required.

``composer require asseco-voice/laravel-json-search``

## Usage

Package provides few endpoints out of the box:

- [POST](#post) ``/api/search/{model}`` - for search 
- [PUT](#put) ``/api/search/{model}/update`` - for mass update by query results
- [DELETE](#delete) ``/api/search/{model}`` - for mass delete by query results

Model should be provided in standard Laravel notation (lowercase plural) in order
to map it automatically (i.e. `/api/search/contacts` in order to search `Contact` model).

By default, ``App`` namespace is used, but you can change the defaults or add additional
endpoints if you have need for that in the [package configuration](#configuration) by either
adding a direct model mapping in ``model_mapping`` key (taking precedence over other
options), or adding additional values to ``models_namespaces`` array to make it more generic. 

Description on how to use each of those are in the configuration file.

If out-of-the-box solutions don't suit you, feel free to implement the logic directly
within your controller. For details check out [custom endpoints section](#custom-endpoints).

Following are some examples, however there is **much more** to the search package than 
just filtering by attributes. 

**For detailed engine usage and logic, refer to 
[this readme](https://github.com/asseco-voice/laravel-json-query-builder).**

## Examples 

### POST

Call the endpoint providing the following JSON:

```json
{
    "search": {
        "first_name": "=foo;bar;!baz",
        "last_name": "=test"
    }
}
```
    
This will perform a ``SELECT * FROM some_table WHERE first_name IN ('foo, 'bar') 
AND first_name not in ('baz') or last_name in ('test')``.

Additionally, you are able to provide ``append`` array to resolve your custom defined 
properties on a Laravel model which aren't listed in `$appends` array. I.e.

```php
public function getSomeAttribute()
{
    return 'foo';
}
```

You can return it by using:

```json
{
    "search": {
        "first_name": "=foo;bar;!baz",
        "last_name": "=test"
    },
    "append": ["some"]
}
```

It is possible to do 1-level nested appends like:

```
"append": ["relation1.append_attribute_for_r1"]
```

More than 1-level is not supported.

Do note that this is completely unoptimized and will potentially cause really slow executions.
Use with caution.

### PUT

Call the endpoint providing the following JSON:

```json
{
    "search": {
        "first_name": "=foo;bar;!baz",
        "last_name": "=test"
    },
    "update": {
        "first_name": "new name"
    }
}
```
    
This will perform a ``SELECT * FROM some_table WHERE first_name IN ('foo, 'bar') 
AND first_name not in ('baz') or last_name in ('test')``, and on the given result
set it will perform a mass update giving a ``new name`` to every record retrieved

### DELETE

```json
{
    "search": {
        "first_name": "=foo;bar;!baz",
        "last_name": "=test"
    }
}
```
    
This will perform a ``DELETE FROM some_table WHERE first_name IN ('foo, 'bar') 
AND first_name not in ('baz') or last_name in ('test')`` doing a mass delete
by given parameters.

## Custom endpoints

It is possible to create a custom endpoint if the current setup does not suit you.

### Search 

- Add route:

```php
Route::post('search', 'ExampleController@search');
```

- Call the method within the controller and provide it with input parameters from JSON body.

```php
public function search(Request $request)
{
    return SomeModel::jsonSearch($request->all())->get();
}
```

### Update

- Add route:

```php
Route::put('search/update', 'ExampleController@search');
```

- Call the method within the controller and provide it with input parameters from JSON body.

```php
public function search(Request $request)
{
    $search = SomeModel::jsonSearch($request->except('update'));

    if (!$request->has('update')) {
        throw new Exception('Missing update parameters');
    }

    $search->update($request->update);

    return $search->get();
}
```

### Delete

- Add route:

```php
Route::delete('search', 'ExampleController@search');
```

- Call the method within the controller and provide it with input parameters from JSON body.

```php
public function search(Request $request)
{
    return SomeModel::jsonSearch($request->all())->delete();
}
```

## Search favorites

Favorites enable you to save searches for a specific user.
 
Usage:

1. Run `php artisan migrate`.
1. Use through standard laravel API resource routes on ``/api/search-favorites`` URL.
1. If you need to modify migrations [publish the package](#configuration) and set `runs_migrations`
property in the config file to ``false``.
2. Set authorizeResource in asseco-search to false if you do not want $this->authorizeResource(SearchFavorite::class) to be added in controller.

It is possible to extend the model used for search favorites and replace with your own. Make sure
your model extends ``SearchFavorite`` and replace `search_favorite_model` key in the configuration 
with your model.

## Debugging

If you'd like to see query called instead of a result, uncomment ``dump`` line
within ``Asseco\JsonSearch\SearchServiceProvider``. 

Due to Laravel query builder inner workings, this will not dump the resulting query for relations. For that purpose
I'd recommend using Laravel query log. 

# Extending the package

Publishing the configuration will enable you to change package models as
well as controlling how migrations behave. If extending the model, make sure
you're extending the original model in your implementation.
