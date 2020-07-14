<?php

namespace Voice\SearchQueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/Config/asseco-voice.php' => config_path('asseco-voice.php'),]);

        Builder::macro('search', function (Request $request) {
            /**
             * @var $this Builder
             */
            $searcher = new Searcher($this, $request);
            $searcher->search();

            return $this;
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/asseco-voice.php', 'asseco-voice');
    }

}
