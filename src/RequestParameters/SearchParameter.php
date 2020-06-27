<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Illuminate\Support\Facades\Config;
use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\OperatorsConfig;
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
        foreach ($operatorsConfig->registeredCallbacks() as $callbackClassName) {
            $operator = $callbackClassName::getCallbackOperator();

            $argumentHasOperator = strpos($argument, $operator) !== false;
            if (!$argumentHasOperator) {
                continue;
            }

            $searchModel = new Search($this->builder->getModel(), $argument, $callbackClassName::getCallbackOperator());
            /**
             * @var AbstractCallback $callback
             */
            $callback = new $callbackClassName($this->builder, $searchModel);

            $callbackType = $operatorsConfig->getCallbackType($callback, $searchModel->type);

            $searchModel->values = $callbackType->prepare($searchModel->values);

            $this->checkForbidden($searchModel->column);

            $callback->execute();
            return;
        }

        throw new SearchException("[Search] No valid callback registered for $argument. Are you missing an operator?");
    }

    /**
     * Check if global forbidden key is used
     *
     * @param string $parameter
     * @throws SearchException
     */
    protected function checkForbidden(string $parameter)
    {
        $forbiddenKeys = Config::get('asseco-voice.search.globalForbiddenColumns');
        $forbiddenKeys = $this->configModel->getForbidden($forbiddenKeys);

        if (in_array($parameter, $forbiddenKeys)) {
            throw new SearchException("[Search] Searching by '$parameter' field is forbidden. Check the configuration if this is not a desirable behavior.");
        }
    }
}
