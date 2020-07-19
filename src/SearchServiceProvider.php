<?php

namespace Voice\JsonSearch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use Voice\JsonQueryBuilder\JsonQuery;

class SearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Builder::macro('search', function (array $input) {
            /**
             * @var $this Builder
             */
            $jsonQuery = new JsonQuery($this, $input);
            $jsonQuery->search();
            //dd($this->dump());
            return $this;
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }

}
