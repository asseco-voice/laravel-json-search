# Laravel search query builder

This package enables ``search`` method on Eloquent models for 
Laravel 7.

PHP min version: 7.4.

## Installation

Package is installed through composer and is automatically registered
as a Laravel service provider.

``composer require asseco-voice/laravel-stomp``

In order to connect it to your queue you need to change queue
connection driver in ``.env`` file:

```
QUEUE_CONNECTION=stomp
```

``.env`` variables you can override:

```
STOMP_QUEUE         queue name (defaults to 'default')
STOMP_PROTOCOL      protocol (defaults to TCP)
STOMP_HOST          broker host (defaults to 127.0.0.1)
STOMP_PORT          port where STOMP is exposed in your broker (defaults to 61613)
STOMP_USERNAME      broker username (defaults to admin)
STOMP_PASSWORD      broker password (defaults to admin)
STOMP_WORKER        job worker to be used (defaults to 'default' can be 'horizon')
```

If ``horizon`` is used as worker, library will work side-by-side with 
[Laravel Horizon](https://laravel.com/docs/7.x/horizon) and basic configuration will be 
automatically resolved:

```
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'stomp',
            'queue' => [env('STOMP_QUEUE', 'default')],
            ...
        ],
    ],

    'local' => [
        'supervisor-1' => [
            'connection' => 'stomp',
            'queue' => [env('STOMP_QUEUE', 'default')],
            ...
        ],
    ],
],
```

If you need a custom configuration, publish Horizon config (check Horizon documentation)
and adapt to your needs. 

## Usage

You can use library now like being native Laravel queue. 
For usage you can check 
[official Laravel queue documentation](https://laravel.com/docs/7.x/queues)
