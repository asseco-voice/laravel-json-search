<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://github.com/asseco-voice/art/blob/main/asseco_logo.png" width="500"></a></p>

# Laravel JSON search

This package exposes a ``search`` method on Laravel Eloquent models
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
endpoints if you have need for that in the [package configuration](#configuration) by adding
additional values to ``models_namespaces`` array. 

Following are some examples, however there is **much more** to the search package than 
just filtering by attributes. 

**For detailed engine usage and logic, refer to 
[this readme](https://github.com/asseco-voice/laravel-json-query-builder).**

## Examples 

### POST

Call the endpoint providing the following JSON:

```
{
    "search": {
        "first_name": "=foo;bar;!baz",
        "last_name": "=test"
    }
}
```
    
This will perform a ``SELECT * FROM some_table WHERE first_name IN ('foo, 'bar') 
AND first_name not in ('baz') or last_name in ('test')``.

### PUT

Call the endpoint providing the following JSON:

```
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

```
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

```
Route::post('search', 'ExampleController@search');
```

- Call the method within the controller and provide it with input parameters from JSON body.

```
public function search(Request $request)
{
    return SomeModel::search($request->all())->get();
}
```

### Update

- Add route:

```
Route::put('search/update', 'ExampleController@search');
```

- Call the method within the controller and provide it with input parameters from JSON body.

```
public function search(Request $request)
{
    $search = SomeModel::search($request->except('update'));

    if (!$request->has('update')) {
        throw new Exception('Missing update parameters');
    }

    $search->update($request->update);

    return $search->get();
}
```

### Delete

- Add route:

```
Route::delete('search', 'ExampleController@search');
```

- Call the method within the controller and provide it with input parameters from JSON body.

```
public function search(Request $request)
{
    return SomeModel::search($request->all())->delete();
}
```

## Search favorites

By default, favorites are disabled. To enable them, set the ``SEARCH_FAVORITES_ENABLED`` 
in your `.env` file to `true`.

Favorites enable you to save searches for a specific user, so after you enable them through
``.env`` you need to run `php artisan migrate`, and routes for favorites will be exposed 
automatically. 

## Debugging

If you'd like to see query called instead of a result, uncomment ``dump`` line
within ``Voice\JsonSearch\SearchServiceProvider``. 

Due to Laravel query builder inner workings, this will not dump the resulting query for relations. For that purpose
I'd recommend using Laravel query log. 

## Configuration

Publish and override the configuration for the package:

    php artisan vendor:publish --provider="Voice\JsonSearch\SearchServiceProvider"
