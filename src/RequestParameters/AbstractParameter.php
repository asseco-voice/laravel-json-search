<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Voice\SearchQueryBuilder\ModelConfig;
use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\Traits\RemovesEmptyValues;

abstract class AbstractParameter
{
    use RemovesEmptyValues;

    /**
     * Constant by which arguments will be split. E.g. column=value\column2=value2
     */
    const ARGUMENT_SEPARATOR = '\\';

    public Request     $request;
    public Builder     $builder;
    public ModelConfig $configModel;

    public function __construct(Request $request, Builder $builder, ModelConfig $configModel)
    {
        $this->request = $request;
        $this->builder = $builder;
        $this->configModel = $configModel;
    }

    /**
     * Query string parameter/key name
     * @return string
     */
    abstract public function getParameterName(): string;

    /**
     * Append the query to Eloquent builder
     * @throws SearchException
     */
    abstract public function appendQuery(): void;

    /**
     * Return query string arguments array (split sting by separator).
     * If argument is not present, alternative fetch will be made if applicable.
     *
     * Input: column=value\column2=value2
     *
     * Output: column=value
     *         column2=value2
     *
     * @return array
     * @throws SearchException
     */
    protected function getArguments(): array
    {
        $parameterName = $this->getParameterName();

        if (!$this->request->has($parameterName)) {
            return $this->fetchAlternative();
        }

        $input = $this->request->query($parameterName);
        $matched = $this->matchWithinParenthesis($input, $parameterName);
        $explodedParameters = explode(self::ARGUMENT_SEPARATOR, $matched[1]);
        $parameters = $this->removeEmptyValues($explodedParameters);

        if (count($parameters) < 1) {
            throw new SearchException("[Search] Couldn't match parameters for '$parameterName' query string. Input found: $input. Did you include anything within parenthesis?");
        }

        return $parameters;
    }

    /**
     * Provide additional method as a fallback if query string argument is not present.
     * Empty array is a valid default, meaning no fallback is available.
     * Override if fallback is needed.
     *
     * @return array
     */
    protected function fetchAlternative(): array
    {
        return [];
    }

    /**
     * Matching everything within parenthesis.
     * @param $input
     * @param string $parameterName
     * @return mixed
     * @throws SearchException
     */
    protected function matchWithinParenthesis($input, string $parameterName)
    {
        preg_match('/\((.*?)\)/', $input, $matched);

        if (count($matched) < 2) {
            throw new SearchException("[Search] Couldn't match anything for '$parameterName' query string. Input found: $input. Are you missing a parenthesis?");
        }

        return $matched;
    }
}
