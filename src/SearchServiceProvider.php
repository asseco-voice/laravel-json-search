<?php

namespace Voice\SearchQueryBuilder;

use App\Providers\Searcher;
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
        $this->publishes([__DIR__ . '/config/asseco-voice.php' => config_path('asseco-voice.php'),]);

        Builder::macro('search', function (Request $request) {
            /**
             * @var $this Builder
             */
            new Searcher($this, $request);
            //dd($this->dump());
            return $this;
            // $query->when(str_contains($attribute, '.'), callback1, callback2)
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/asseco-voice.php', 'asseco-voice');
    }

}
