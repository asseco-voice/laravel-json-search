<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Illuminate\Support\Facades\Config;
use Voice\SearchQueryBuilder\Exceptions\SearchException;

class Search extends AbstractParameter
{
    /**
     * Constant by which values will be split within a single parameter. E.g. parameter=value1;value2
     */
    const VALUE_SEPARATOR = ';';

    protected array $types = [
        '{b}'  => 'boolean',
        '{d}'  => 'date',
        '{t}'  => 'time',
        '{dt}' => 'datetime',
        '{n}'  => 'number',
    ];

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
        if (!$this->request->has($this->getParameterName())) {
            throw new SearchException("[Search] Couldn't match anything for '" . $this->getParameterName() . "' query string.");
        }

        return $this->getRawParameters($this->getParameterName());
    }

    /**
     * Append the query based on the given parameters
     *
     * @param $searchParameter
     * @throws SearchException
     */
    protected function appendSearchQuery(string $searchParameter): void
    {
        [$operator, $callback] = $this->operatorCallbacks->parseOperatorAndCallback($searchParameter);
        [$column, $values, $type] = $this->parseSearchParameterValues($searchParameter, $operator);

        $this->checkForbidden($column);

        $splitValues = $this->splitValues($searchParameter, $values);

        call_user_func($callback, $column, $splitValues, $type);
    }

    /**
     * Exploding by a first occurrence of the operator to get the parameter key and value separated
     *
     * @param $searchParameter
     * @param $operator
     * @return array
     * @throws SearchException
     */
    protected function parseSearchParameterValues($searchParameter, $operator): array
    {
        $split = explode($operator, $searchParameter, 2);

        if (count($split) != 2) {
            throw new SearchException("[Search] Invalid search parameter(s): " . print_r($split, true));
        }

        $parameter = $split[0];
        $values = $split[1];

        [$type, $parameter] = $this->getParameterType($parameter);

        return [$parameter, $values, $type];
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
        $forbiddenKeys = $this->searchModel->getForbidden($forbiddenKeys);

        if (in_array($parameter, $forbiddenKeys)) {
            throw new SearchException("[Search] Searching by '$parameter' field is forbidden. Check the configuration if this is not a desirable behavior.");
        }
    }

    /**
     * Check for parameter type (will be applied to all values)
     *
     * @param $parameter
     * @return array
     */
    protected function getParameterType($parameter): array
    {
        $type = null;

        foreach ($this->types as $typeKey => $name) {
            if (strpos($parameter, $typeKey) !== false) {
                $type = $name;
                $parameter = str_replace($typeKey, '', $parameter);
                break;
            }
        }

        return [$type, $parameter];
    }

    /**
     * Split values by a given separator
     *
     * Input: val1;val2
     *
     * Output: val1
     *         val2
     *
     * @param $parameter
     * @param $values
     * @return array
     * @throws SearchException
     */
    protected function splitValues(string $parameter, string $values): array
    {
        $valueArray = explode(self::VALUE_SEPARATOR, $values);
        $cleanedUpValues = $this->removeEmptyValues($valueArray);

        if (count($cleanedUpValues) < 1) {
            throw new SearchException("[Search] Parameter $parameter is missing a value.");
        }

        return $cleanedUpValues;
    }

}
