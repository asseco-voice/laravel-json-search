<?php

use Asseco\JsonSearch\App\Models\SearchFavorite;

return [
    'search_favorite_model' => SearchFavorite::class,

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
    'models_namespaces'        => [
        'App\Models',
    ],

    /**
     * If favorites are enabled, migrations and routes for this feature
     * will be enabled as well. Defaults to false.
     */
    'search_favorites_enabled' => env('SEARCH_FAVORITES_ENABLED', false) === true,

    /**
     * Path to original stub which will create the migration upon publishing.
     */
    'stub_path' => '/../migrations/create_search_favorites_table.php.stub',
];
