<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\OperatorCallbacks;
use Voice\SearchQueryBuilder\SearchModel;

abstract class AbstractParameter
{
    /**
     * Constant by which parameters will be split. E.g. parameter=value\parameter2=value2
     */
    const PARAMETER_SEPARATOR = '\\';

    public Request                  $request;
    public SearchModel              $searchModel;
    public Builder                  $builder;
    public OperatorCallbacks        $operatorCallbacks;

    public function __construct(Request $request, Builder $builder)
    {
        $this->request = $request;
        $this->builder = $builder;
        $this->searchModel = new SearchModel($this->builder->getModel());
        $this->operatorCallbacks = new OperatorCallbacks($this->builder, $this->searchModel);
    }

    /**
     * Get name by which the parameter will be fetched
     * @return string
     */
    abstract public function getParameterName(): string;

    /**
     * Append the query to Eloquent builder
     * @throws SearchException
     */
    abstract public function appendQuery(): void;

    /**
     * Return key-value pairs array from query string parameter
     *
     * @return array
     * @throws SearchException
     */
    abstract public function parse(): array;

    /**
     * Extract raw string from parenthesis provided in the query string.
     *
     * @param string $inputType
     * @return mixed
     * @throws SearchException
     */
    protected function getRawParameters(string $inputType): array
    {
        $input = $this->request->query($inputType);
        // Match everything within parenthesis ( ... )
        preg_match('/\((.*?)\)/', $input, $matched);

        if (count($matched) < 2) {
            throw new SearchException("[Search] Couldn't match anything for '$inputType' query string. Input found: $input. Are you missing a parenthesis?");
        }

        $explodedParameters = explode(self::PARAMETER_SEPARATOR, $matched[1]);
        $parameters = $this->removeEmptyValues($explodedParameters);

        if (count($parameters) < 1) {
            throw new SearchException("[Search] Couldn't match parameters for '$inputType' query string. Input found: $input. Did you include anything within parenthesis?");
        }

        return $parameters;
    }

    /**
     * Remove empty values from a given array
     *
     * @param array $input
     * @return array
     */
    protected function removeEmptyValues(array $input): array
    {
        $trimmedInput = array_map('trim', $input);

        $deleteKeys = array_keys(array_filter($trimmedInput, function ($item) {
            return $item == '';
        }));

        foreach ($deleteKeys as $deleteKey) {
            unset($trimmedInput[$deleteKey]);
        }

        return $trimmedInput;
    }
}
