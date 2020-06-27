<?php

namespace Voice\SearchQueryBuilder\SearchCallbacks;

use Illuminate\Database\Eloquent\Builder;
use Voice\SearchQueryBuilder\Exceptions\SearchException;

class Equals extends AbstractCallback
{
    const OPERATOR = '=';

    /**
     * Execute a callback on a given column, providing the array of values
     *
     * @throws SearchException
     */
    public function execute(): void
    {
        // $query->when(str_contains($column, '.'), callback1, callback2)

        if (str_contains($this->searchModel->column, '.')) {

            [$relationName, $relationAttribute] = explode('.', $this->searchModel->column);

            $values = $this->searchModel->values;

            // TODO: this of course doesn't work --- test test test
            $this->builder->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $values) {
                $query->where($relationAttribute, $values[0]);
            });
            return;
        }


        $andValues = [];
        $notValues = [];

        foreach ($this->searchModel->values as $value) {
            if ($this->isNegated($value)) {
                $value = str_replace('!', '', $value);

                if ($this->hasWildCard($value)) {
                    $this->builder->where($this->searchModel->column, 'NOT LIKE', $this->replaceWildCard($value));
                    continue;
                }

                $notValues[] = $value;
                continue;
            }

            if ($this->hasWildCard($value)) {
                $this->builder->where($this->searchModel->column, 'LIKE', $this->replaceWildCard($value));
                continue;
            }

            $andValues[] = $value;
        }

        if (count($andValues) > 0) {
            $this->builder->orWhereIn($this->searchModel->column, $andValues);
        }
        if (count($notValues) > 0) {
            $this->builder->whereNotIn($this->searchModel->column, $notValues);
        }
    }
}
