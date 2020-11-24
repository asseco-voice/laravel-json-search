<?php

declare(strict_types=1);

namespace Voice\JsonSearch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Voice\JsonQueryBuilder\JsonQuery;

class SearchServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/asseco-search.php', 'asseco-search');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $favoritesEnabled = config('asseco-search.search_favorites_enabled');

        if ($favoritesEnabled) {
            $this->loadMigrationsFrom(__DIR__.'/../migrations');
        }
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/asseco-search.php' => config_path('asseco-search.php')]);

        Builder::macro('search', function (array $input) {
            /**
             * @var $this Builder
             */
            $jsonQuery = new JsonQuery($this, $input);
            $jsonQuery->search();
            //$this->dd();
            return $this;
        });
    }
}
