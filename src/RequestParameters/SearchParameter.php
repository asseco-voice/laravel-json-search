<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Config\OperatorsConfig;
use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\RequestParameters\Models\Search;
use Voice\SearchQueryBuilder\SearchCallbacks\AbstractCallback;

class SearchParameter extends AbstractParameter
{
    /**
     * Get name by which the parameter will be fetched
     * @return string
     */
    public function getParameterName(): string
    {
        return 'search';
    }

    /**
     * Append the query to Eloquent builder
     * @throws SearchException
     */
    public function appendQuery(): void
    {
        $arguments = $this->getArguments();
        $operatorsConfig = new OperatorsConfig();

        $this->builder->where(function () use ($arguments, $operatorsConfig) {
            foreach ($arguments as $argument) {
                $this->appendSingle($argument, $operatorsConfig);
            }
        });
    }

    /**
     * Append the query based on the given argument
     *
     * @param string $argument
     * @param OperatorsConfig $operatorsConfig
     * @throws SearchException
     */
    protected function appendSingle(string $argument, OperatorsConfig $operatorsConfig): void
    {
        $searchModel = new Search($this->builder->getModel(), $this->modelConfig, $argument);
        $callbackClassName = $operatorsConfig->getCallbackClassFromOperator($searchModel->operator);

        /**
         * @var AbstractCallback $callback
         */
        new $callbackClassName($this->builder, $searchModel, $operatorsConfig);
    }
}
