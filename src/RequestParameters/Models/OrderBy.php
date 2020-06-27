<?php

namespace Voice\SearchQueryBuilder\RequestParameters\Models;

use Voice\SearchQueryBuilder\Exceptions\SearchException;

class OrderBy
{
    const DEFAULT_DIRECTION = 'asc';

    protected array $arguments;

    /**
     * OrderBy constructor.
     * @param array $arguments
     * @throws SearchException
     */
    public function __construct(array $arguments)
    {
        if (count($arguments) == 0) {
            throw new SearchException("[Search] Something went wrong with ordering.");
        }

        $this->arguments = $arguments;
    }

    public function column(): string
    {
        return $this->arguments[0];
    }

    public function direction(): string
    {
        $direction = self::DEFAULT_DIRECTION;

        if (count($this->arguments) > 1) {
            $direction = $this->arguments[1];
        }

        return $direction;
    }
}
