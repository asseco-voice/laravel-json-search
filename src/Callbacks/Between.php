<?php

namespace Voice\SearchQueryBuilder\Callbacks;

class Between extends AbstractCallback
{
    /**
     * Execute a callback on a given column, providing the array of values
     *
     * @param string $column
     * @param array $values
     * @param string $type
     * @throws \Voice\SearchQueryBuilder\Exceptions\SearchException
     */
    function execute(string $column, array $values, string $type)
    {
        $this->betweenCallback($column, $values, '<>');
    }
}
