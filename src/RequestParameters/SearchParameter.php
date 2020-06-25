<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Illuminate\Support\Facades\Config;
use Voice\SearchQueryBuilder\Callbacks\AbstractCallback;
use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\OperatorCallbacks;
use Voice\SearchQueryBuilder\RequestParameters\Models\Search;

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
        $parameters = $this->parse();

        $this->builder->where(function () use ($parameters) {
            foreach ($parameters as $parameter) {
                $this->appendSearchQuery($parameter);
            }
        });
    }

    /**
     * Return key-value pairs array from query string parameter
     *
     * @return array
     * @throws SearchException
     */
    function parse(): array
    {
        $parameter = $this->getParameterName();

        if (!$this->request->has($parameter)) {
            throw new SearchException("[Search] Couldn't match anything for '" . $parameter . "' query string.");
        }

        return $this->getRawParameters($parameter);
    }

    /**
     * Append the query based on the given parameters
     *
     * @param $searchParameter
     * @throws SearchException
     */
    protected function appendSearchQuery(string $searchParameter): void
    {
        $operatorCallback = new OperatorCallbacks($this->builder, $searchParameter);
        $searchModel = new Search($searchParameter, $operatorCallback->operator);

        $this->checkForbidden($searchModel->column);

        /**
         * @var AbstractCallback $callback
         */
        $callback = new $operatorCallback->callback($this->builder);
        $callback->execute($searchModel->column, $searchModel->values, $searchModel->type);
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
