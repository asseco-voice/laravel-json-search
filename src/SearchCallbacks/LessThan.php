<?php

namespace Voice\SearchQueryBuilder\SearchCallbacks;

use Illuminate\Database\Eloquent\Builder;
use Voice\SearchQueryBuilder\CategorizedValues;

class LessThan extends AbstractCallback
{
    const OPERATOR = '<';

    /**
     * Execute a callback on a given column, providing the array of values
     *
     * @param Builder $builder
     * @param string $column
     * @param CategorizedValues $values
     * @throws \Voice\SearchQueryBuilder\Exceptions\SearchException
     */
    public function execute(Builder $builder, string $column, CategorizedValues $values): void
    {
        $this->lessOrMoreCallback($builder, $column, $values, '<');
    }
}
