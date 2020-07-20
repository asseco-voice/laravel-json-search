# Laravel JSON search

This package enables ``search`` method on Laravel Eloquent models
providing a detailed DB search with JSON as input parameter. 

It functions out-of-the-box automatically for all Eloquent models 
within the project. No additional setup is needed.

## Installation

Install the package through composer. It is automatically registered
as a Laravel service provider, so no additional actions are required.

``composer require asseco-voice/laravel-json-search``

## Quick usage

Create a POST search endpoint

```
Route::post('search', 'ExampleController@search');
```

Call the method within the controller and provide it with input parameters from JSON body.

```
public function search(Request $request)
{
    return SomeModel::search($request->all())->get();
}
```
 
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

## In depth

For detailed engine usage and logic, refer to 
[this readme](https://github.com/asseco-voice/laravel-json-query-builder).
