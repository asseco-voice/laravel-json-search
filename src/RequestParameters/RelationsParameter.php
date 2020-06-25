<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Exceptions\SearchException;

class RelationsParameter extends AbstractParameter
{
    /**
     * Get name by which the parameter will be fetched
     * @return string
     */
    public function getParameterName(): string
    {
        return 'relations';
    }

    /**
     * Append the query to Eloquent builder
     * @throws SearchException
     */
    public function appendQuery(): void
    {
        $parameters = $this->parse();

        $this->builder->with($parameters);
    }

    /**
     * Return key-value pairs array from query string parameter
     *
     * @return array
     * @throws SearchException
     */
    public function parse(): array
    {
        $parameter = $this->getParameterName();

        if ($this->request->has($parameter)) {
            return $this->getRawParameters($parameter);
        }

        return $this->configModel->getRelations();
    }
}
