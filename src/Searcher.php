<?php

namespace Voice\SearchQueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Voice\SearchQueryBuilder\Config\ModelConfig;
use Voice\SearchQueryBuilder\Config\RequestParametersConfig;
use Voice\SearchQueryBuilder\RequestParameters\AbstractParameter;

class Searcher
{
    protected Builder                 $builder;
    protected Request                 $request;
    protected ModelConfig             $modelConfig;
    protected RequestParametersConfig $requestParametersConfig;

    /*
     * TODO:
     * datum od danas toliko dana
     *
     * paginacija
     *
     * or/and?
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
        $this->modelConfig = new ModelConfig($builder->getModel());
        $this->requestParametersConfig = new RequestParametersConfig();
    }

    /**
     * Perform the search
     *
     * @throws Exceptions\SearchException
     */
    public function search(): void
    {
        $this->appendQueries();
        Log::info('[Search] SQL: ' . $this->builder->toSql() . " Bindings: " . implode(', ', $this->builder->getBindings()));
    }

    /**
     * Append all queries from registered parameters
     *
     * @throws Exceptions\SearchException
     */
    protected function appendQueries(): void
    {
        foreach ($this->requestParametersConfig->registered as $parameter) {
            $requestParameter = $this->createRequestParameter($parameter);
            $requestParameter->appendQuery();
        }
    }

    protected function createRequestParameter($parameter): AbstractParameter
    {
        return new $parameter($this->request, $this->builder, $this->modelConfig);
    }
}
