<?php

namespace Voice\SearchQueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Voice\SearchQueryBuilder\RequestParameters\AbstractParameter;

class Searcher
{
    protected Builder $builder;
    protected Request $request;
    protected array   $requestParameters;

    /*
     * TODO:
     * datum od danas toliko dana
     * is null is not null
     * bool isprobaj 1/0
     * relacije
     *
     * forsiraj type -> riješi tipove one
     *
     * or?
     *
     * https://freek.dev/1182-searching-models-using-a-where-like-query-in-laravel
     *
     */

    /**
     * Searcher constructor.
     * @param Builder $builder
     * @param Request $request
     * @throws Exceptions\SearchException
     */
    public function __construct(Builder $builder, Request $request)
    {
        $this->builder = $builder;
        $this->request = $request;

        $this->requestParameters = Config::get('asseco-voice.search.registeredRequestParameters');
    }

    /**
     * Perform the search
     *
     * @throws Exceptions\SearchException
     */
    public function search(): void
    {
        $this->appendQueries();
        Log::info('[Search] SQL: ' . $this->builder->toSql());
    }

    /**
     * Append all queries from registered parameters
     *
     * @throws Exceptions\SearchException
     */
    protected function appendQueries(): void
    {
        /**
         * @var AbstractParameter $instance
         */
        foreach ($this->requestParameters as $parameter) {
            $instance = new $parameter($this->request, $this->builder);
            $instance->appendQuery();
        }
    }
}
