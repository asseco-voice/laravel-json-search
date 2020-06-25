<?php

namespace Voice\SearchQueryBuilder\Callbacks;

class NotEquals extends AbstractCallback
{
    /**
     * Execute a callback on a given column, providing the array of values
     *
     * @param string $column
     * @param array $values
     * @param string $type
     */
    function execute(string $column, array $values, string $type)
    {
        $notValues = [];

        foreach ($values as $value) {
            if ($this->isNegated($value)) {
                $value = str_replace('!', '', $value);
            }

            if ($this->hasWildCard($value)) {
                $this->builder->where($column, 'NOT LIKE', $this->replaceWildCard($value));
                continue;
            }

            $notValues[] = $value;
            continue;
        }

        if (count($notValues) > 0) {
            $this->builder->whereNotIn($column, $notValues);
        }
    }
}
