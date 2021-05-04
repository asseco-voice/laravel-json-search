<?php

declare(strict_types=1);

namespace Asseco\JsonSearch;

use Asseco\JsonQueryBuilder\JsonQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/asseco-search.php', 'asseco-search');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        if (config('asseco-search.runs_migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        }
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../migrations' => database_path('migrations'),
        ], 'asseco-search');

        $this->publishes([
            __DIR__ . '/../config/asseco-search.php' => config_path('asseco-search.php'),
        ], 'asseco-search');

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
