<?php

use Asseco\JsonSearch\App\SearchFavorite;

return [
    'search_favorite_model' => SearchFavorite::class,

    /**
     * Directly map a model name to a class. This takes precedence over model_namespaces.
     */
    'model_mapping' => [
        // 'model' => Model::class,
    ],

    /**
     * These namespaces will be used to automatically map models within search controller.
     * First model to be found will be returned.
     */
    'models_namespaces'        => [
        'App',
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
