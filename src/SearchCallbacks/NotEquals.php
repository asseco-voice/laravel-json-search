<?php

namespace Voice\SearchQueryBuilder\SearchCallbacks;

use Voice\SearchQueryBuilder\Exceptions\SearchException;

class NotEquals extends AbstractCallback
{
    const OPERATOR = '!=';

    /**
     * Execute a callback on a given column, providing the array of values
     *
     * @throws SearchException
     */
    public function execute(): void
    {
        $notValues = [];

        foreach ($this->searchModel->values as $value) {
            if ($this->isNegated($value)) {
                $value = str_replace('!', '', $value);
            }

            if ($this->hasWildCard($value)) {
                $this->builder->where($this->searchModel->column, 'NOT LIKE', $this->replaceWildCard($value));
                continue;
            }

            $notValues[] = $value;
            continue;
        }

        if (count($notValues) > 0) {
            $this->builder->whereNotIn($this->searchModel->column, $notValues);
        }
    }
}
