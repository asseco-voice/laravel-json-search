<?php

namespace Voice\SearchQueryBuilder\RequestParameters\Models;

use Voice\SearchQueryBuilder\Exceptions\SearchException;

class OrderBy
{
    const DEFAULT_DIRECTION = 'asc';

    protected array $parameters;

    /**
     * OrderBy constructor.
     * @param array $parameters
     * @throws SearchException
     */
    public function __construct(array $parameters)
    {
        if (count($parameters) == 0) {
            throw new SearchException("[Search] Something went wrong with ordering.");
        }

        $this->parameters = $parameters;
    }

    public function column(): string
    {
        return $this->parameters[0];
    }

    public function direction(): string
    {
        $direction = self::DEFAULT_DIRECTION;

        if (count($this->parameters) > 1) {
            $direction = $this->parameters[1];
        }

        return $direction;
    }
}
