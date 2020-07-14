<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Config\OperatorsConfig;
use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\RequestParameters\Models\Search;
use Voice\SearchQueryBuilder\SearchCallbacks\AbstractCallback;

class SearchParameter extends AbstractParameter
{
    public function getParameterName(): string
    {
        return 'search';
    }

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
