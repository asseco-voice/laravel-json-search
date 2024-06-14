<?php

use Asseco\BlueprintAudit\App\MigrationMethodPicker;
use Asseco\JsonSearch\App\Models\SearchFavorite;

return [

    /**
     * Model bindings.
     */
    'models' => [
        'search_favorite' => SearchFavorite::class,
    ],

    'migrations' => [

        /**
         * UUIDs as primary keys.
         */
        'uuid' => false,

        /**
         * Timestamp types.
         *
         * @see https://github.com/asseco-voice/laravel-common/blob/master/config/asseco-common.php
         */
        'timestamps' => MigrationMethodPicker::PLAIN,

        /**
         * Should the package run the migrations. Set to false if you're publishing
         * and changing default migrations.
         */
        'run' => true,
    ],

    'authorization' => [
        /**
         * Should the authorizeResource() be added to SearchFavorite controller. Set to false if you do
         * not have authorization implemented
         */
      'authorizeResource' => true,
    ],

    /**
     * Directly map a model name to a class or query builder instance
     * through a callback. This takes precedence over model_namespaces.
     *
     * You CAN'T add query builder instance without a callback as it
     * will boot with the framework and cause errors in the process.
     */
    'model_mapping' => [
        // 'model' => Model::class,
        // 'model' => fn() => Model::someScope(),
        // 'model' => function() { return Model::someScope(); }
    ],

    /**
     * These namespaces will be used to automatically map models within search controller.
     * First model to be found will be returned.
     */
    'models_namespaces' => [
        'App\Models',
    ],

    'routes' => [
        'prefix' => 'api',
        'middleware' => ['api'],
    ],

    /**
     * Sets the default upper limit for search queries. If the limit parameter is not passed, or the passed
     * limit is higher, it will automatically be set to this value. Leave the value as null if you don't want to use this feature.
     */
    'default_limit' => null,
];
