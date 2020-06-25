<?php

namespace Voice\SearchQueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Voice\SearchQueryBuilder\Exceptions\SearchException;

class OperatorCallbacks
{
    protected Builder $builder;

    public string $operator;
    public string $callback;

    /**
     * OperatorCallbacks constructor.
     * @param Builder $builder
     * @param ConfigModel $configModel
     * @param string $searchParameter
     * @throws SearchException
     */
    public function __construct(Builder $builder, string $searchParameter)
    {
        $this->builder = $builder;

        $this->parse($searchParameter);
    }

    /**
     * Find which callback to use depending on operator provided
     *
     * @param $searchParameter
     * @throws SearchException
     */
    protected function parse(string $searchParameter): void
    {
        $callbacks = Config::get('asseco-voice.search.registeredSearchCallbacks');

        foreach ($callbacks as $operator => $callback) {
            if (strpos($searchParameter, $operator) !== false) {
                $this->operator = $operator;
                $this->callback = $callback;
                return;
            }
        }

        throw new SearchException("[Search] No valid callback registered for given operator: $searchParameter");
    }
}
