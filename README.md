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
Eloquent models and does not require any additional actions to be done on models.
It functions out-of-the-box automatically for all Eloquent models within the project.
 
In order to use it you can call:

```
SomeModel::search($request)->get()
```

Where ``$request`` is meant to be a full `Illuminate\Http\Request` object.
So when called within a controller it would for example look like:

    public function search(Request $request)
    {
        return SomeModel::search($request)->get();
    }
    
It is assumed that Request object contains query string parameters which
are using a specific search logic explained below.

Quickly starting though, you can try out a simple query string example:

```
?search=(first_name=foo;bar;!baz\last_name=test)
```

- ``keys`` - `first_name` and `last_name`
- ``operators`` - `=`
- ``values`` - `foo`, `bar`, `baz`, `test`

Will perform a ``SELECT * FROM some_table WHERE first_name IN ('foo, 'bar') 
AND first_name not in ('baz') or last_name in ('test')``.

## Dev naming conventions for this package

- **parameter** is a query string key name (i.e. `?key=...`)
- **arguments** are considered to be query string values (i.e. `?key=( ... value ...)`),
or more precisely everything coming after ``=`` sign after query string key
    - **argument** is a single key-value pair within parameter values
(i.e. `?key=( key=value, key=value )`). 
        -  single argument is broken down to **column / operator / value** 

## Parameter breakdown
Parameters follow a special logic to query the DB. It is possible to use the following
query string parameters (keys):

- ``search`` - will perform the querying logic. **This key is mandatory** to have, 
all other are optional.
- ``returns`` - will return only the columns provided as values (underlying logic is that 
it actually does `SELECT /keys/ FROM` instead of `SELECT * FROM`)
- ``order-by`` - will order the results based on values provided

### Search

The logic is done in a ``(key operator values)`` fashion in which we assume the 
following:

- ``( ... )`` - everything needs to be enclosed within parenthesis
- `key` represents a column in the database. Multiple keys can be separated with a 
backslash ``\`` i.e. `(key=value\key2=value2)`.
- ``operator`` is one of the available main operators for querying (listed below)
- ``values`` is a semicolon (`;`) separated list of values 
(i.e. `(key=value;value2;value3)`) which
can have micro-operators on them as well (i.e. `key=value;!value2;*value3*`). 

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
- `*` - performs a `LIKE` query. Works only on a beginning, end or both ends of the 
value (i.e. `*value`, `value*` or `*value*`). `*` gets converted to `%`. `%` can't be
used because it is a reserved character in query strings.

```
?search=(first_name=!foo\last_name=bar*)
```

Will perform a ``SELECT * FROM some_table WHERE first_name NOT IN 
('foo') AND last_name LIKE 'bar%'``.

Notice that here ``!value`` behaved the same as ``!=`` main operator. The difference
is that ``!=`` main operator negates the complete list of values, whereas the 
``!value`` only negates that specific value. I.e. `key!=value1;value2` is semantically
the same as ``key=!value1;!value2``.

### Returns

Using a ``returns`` key will effectively only return the fields given within it.
This key is not mandatory, however using it does require following the convention
used. Everything needs to be enclosed within parenthesis ``( ... )``, and separating
values is done in the same fashion as with values within a ``search`` parameter 
- with a backslash ``\``.

Example:

```
?search=(...)&returns=(first_name\last_name)
```

Will perform a ``SELECT first_name, last_name FROM ...``

### Order by

Using ``order-by`` key does an 'order by' based on the given key(s). If no value
is provided to a key, it is assumed that order is ascending. Order of the keys
matters!

Example:

```
?search=(...)&order-by=(first_name\last_name=desc)
```

Will perform a ``SELECT ... ORDER BY first_name asc, last_name desc``

Explicitly saying ``first_name=asc`` would do the same, however using anything
besides ``asc/desc`` as a value will throw an exception. 

### Relations

It is possible to load object relations as well by using ``relations`` parameter.
Same convention is followed:

```
?...&returns=(...\...)
```

Relations, if defined properly and following Laravel convention, should be predictable
to assume:

- 1:M & M:M - relation name is in plural (i.e. Contact has many **Addresses**, relation 
name is thus 'addresses')
- M:1 - relation name is in singular (i.e. Comment belongs to a **Post**, relation
name is thus 'post')

## Config 

Besides standard query string search, it is possible to provide additional 
package configuration.

Publish the configuration by running 
`php artisan vendor:publish --provider="Voice\SearchQueryBuilder\SearchServiceProvider"`.

All the keys within the configuration file have a detailed explanation above each key.
