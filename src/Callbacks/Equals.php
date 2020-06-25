<?php

namespace Voice\SearchQueryBuilder\Callbacks;

class Equals extends AbstractCallback
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
        $andValues = [];
        $notValues = [];

        foreach ($values as $value) {
            if ($this->isNegated($value)) {
                $value = str_replace('!', '', $value);

                if ($this->hasWildCard($value)) {
                    $this->builder->where($column, 'NOT LIKE', $this->replaceWildCard($value));
                    continue;
                }

                $notValues[] = $value;
                continue;
            }

            if ($this->hasWildCard($value)) {
                $this->builder->where($column, 'LIKE', $this->replaceWildCard($value));
                continue;
            }

            $andValues[] = $value;
        }

        if (count($andValues) > 0) {
            $this->builder->orWhereIn($column, $andValues);
        }
        if (count($notValues) > 0) {
            $this->builder->whereNotIn($column, $notValues);
        }
    }
}
