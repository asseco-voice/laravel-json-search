# Laravel search query builder

This package enables ``search`` method on Eloquent models for 
Laravel 7 to enable detailed DB search through URL query string. 

PHP min version: 7.4.

## Installation

Package is installed through composer and is automatically registered
as a Laravel service provider.

``composer require asseco-voice/laravel-search-query-builder``

## Quick usage

This package is meant to provide an additional ``search`` method on already existing 
Eloquent models and does not require any additional actions to be done on the models.
It functions out-of-the-box automatically on a global scale.
 
In order to use it you can call:

```
SomeModel::search($request)->get()
```

Where ``$request`` is meant to be a full `Illuminate\Http\Request` object.
So when called within a controller it would for example look like:

    public function search(Request $request)
    {
        return response()->json([
            'someModel' => SomeModel::search($request)->get()
        ]);
    }
    
Request object is supposed to be a query string which accepts several parameters
using a specific search logic explained below.

Quickly starting though, you can try out a simple query string example:

```
?search=(first_name=foo;bar;!baz\last_name=test)
```

- ``keys`` - `first_name` and `last_name`
- ``operators`` - `=`
- ``values`` - `foo`, `bar`, `baz`, `test`

Will perform a ``SELECT * FROM some_table WHERE first_name IN ('foo, 'bar') 
AND first_name not in ('baz') or last_name in ('test')``.

## Parameter breakdown
Parameters follow a special logic to query the DB. It is possible to use the following
keys inside a query string:

- ``search`` - will perform the querying logic. **This key is mandatory** to have, 
all other are optional.
- ``returns`` - will return only the columns provided here (underlying logic is that 
it actually does `SELECT /keys/ FROM` instead of `SELECT * FROM`)
- ``order-by`` - will order the results based on input parameters

### Search

The logic is done in a ``(key operator values)`` fashion in which we assume the 
following:

- ``( ... )`` - everything needs to be enclosed within parenthesis
- `key` represents a column in the database (multiple keys can be separated with a 
backslash ``\ \`` i.e. `(key=value\key2=value2)`)
- ``operator`` is one of the available main operators for querying (listed below)
- ``values`` is a semicolon (`;`) separated list of values 
(i.e. `(key=value;value2;value3)`) which
can have micro-operators on them as well (i.e. `key=value;!value2;%value3%`). 

#### Main operators

- `=` - equals
- `!=` - does not equal
- `<` - less than (requires exactly one value)
- `>` - greater than (requires exactly one value)
- `<=` - less than or equal (requires exactly one value)
- `>=` - greater than or equal (requires exactly one value)
- `<>` - between (requires exactly two values)
- `!<>` - not between (requires exactly two values)

Example:

```
?search=(first_name=foo\last_name!=bar)
```

Will perform a ``SELECT * FROM some_table WHERE first_name IN 
('foo') AND last_name NOT IN ('bar')``.

#### Micro operators

- `!` - negates the value. Works only on the beginning of the value (i.e. `!value`).
- `%` - performs a `LIKE` query. Works only on a beginning, end or both ends of the 
value (i.e. `%value`, `value%` or `%value%`).

```
?search=(first_name=!foo\last_name=bar%)
```

Will perform a ``SELECT * FROM some_table WHERE first_name NOT IN 
('foo') AND last_name LIKE 'bar%'``.

Notice that here ``!value`` behaved the same as ``!=`` main operator. The difference
is that ``!=`` main operator negates the complete list of values, whereas the 
``!value`` only negates that specific value. I.e. `key!=value1;value2` is semantically
the same as ``key=!value1;!value2``.

### Returns

Using a ``returns`` key will effectively only return the fields given within it.
This key is not mandatory. Separating values is done in the same fashion as with
values within a ``search`` parameter - with semicolon `;`.

Example:

```
?search=(...)&returns=(first_name;last_name)
```

Will perform a ``SELECT first_name, last_name FROM ...``

### Order by

...

## Config 

...
