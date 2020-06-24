<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Exceptions\SearchException;

class Returns extends AbstractParameter
{
    /**
     * Get name by which the attribute will be fetched
     * @return string
     */
    public function getAttributeName(): string
    {
        return 'returns';
    }

    /**
     * Append the query to Eloquent builder
     * @throws SearchException
     */
    public function appendQuery(): void
    {
        $attributes = $this->parse();

        $this->builder->select($attributes);
    }

    /**
     * Return key-value pairs array from query string attribute
     *
     * @return array
     * @throws SearchException
     */
    public function parse(): array
    {
        if ($this->request->has($this->getAttributeName())) {
            return $this->getRawAttributes($this->getAttributeName());
        }

        return $this->searchModel->getReturns();
    }
}
