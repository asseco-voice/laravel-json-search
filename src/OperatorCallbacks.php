<?php

namespace Voice\SearchQueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Voice\SearchQueryBuilder\Exceptions\SearchException;

class OperatorCallbacks
{
    /**
     * Constant declaring what will be used as a negation parameter.
     */
    const NOT = '!';

    protected Builder     $builder;
    protected SearchModel $searchModel;

    /**
     * Registered operators and callbacks they use. Order matters!
     * Operators with more characters must come before those with less.
     */
    protected array $operatorCallbackMapping = [
        '!<>' => 'notBetween',
        '<='  => 'lessThanOrEqual',
        '>='  => 'greaterThanOrEqual',
        '<>'  => 'between',
        '!='  => 'notEquals',
        '='   => 'equals',
        '<'   => 'lessThan',
        '>'   => 'greaterThan',
    ];

    public function __construct(Builder $builder, SearchModel $searchModel)
    {
        $this->builder = $builder;
        $this->searchModel = $searchModel;
    }

    /**
     * Find which callback to use depending on operator provided
     *
     * @param $searchParameter
     * @return array
     * @throws SearchException
     */
    public function parseOperatorAndCallback($searchParameter): array
    {
        foreach ($this->operatorCallbackMapping as $operator => $callbackValue) {
            $callback = [$this, $callbackValue];
            if (strpos($searchParameter, $operator) !== false) {
                if (!is_callable($callback)) {
                    throw new SearchException("[Search] No valid callback registered for given operator: $operator");
                }

                return [$operator, $callback];
            }
        }

        throw new SearchException("[Search] Invalid operator provided: $searchParameter");
    }

    /**
     * @param $key
     * @param $values
     * @throws SearchException
     */
    public function equals($key, $values)
    {
        $andValues = [];
        $notValues = [];

        $type = $this->assumeType($key);

        foreach ($values as $value) {
            if ($this->isNegated($value)) {
                $value = str_replace('!', '', $value);

                if ($this->hasWildCard($value)) {
                    $this->builder->where($key, 'NOT LIKE', $value);
                    continue;
                }

                $notValues[] = $value;
                continue;
            }

            if ($this->hasWildCard($value)) {
                $this->builder->where($key, 'LIKE', $value);
                continue;
            }

            $andValues[] = $value;
        }

        if (count($andValues) > 0) {
            $this->builder->orWhereIn($key, $andValues);
        }
        if (count($notValues) > 0) {
            $this->builder->whereNotIn($key, $notValues);
        }
    }

    public function assumeType(string $key)
    {
        $columns = $this->searchModel->getModelColumns();

        return array_key_exists($key, $columns) ? $columns[$key] : null;
    }

    /**
     * @param $key
     * @param $values
     * @throws SearchException
     */
    public function notEquals($key, $values)
    {
        $notValues = [];

        foreach ($values as $value) {
            if ($this->isNegated($value)) {
                $value = str_replace('!', '', $value);
            }

            if ($this->hasWildCard($value)) {
                $this->builder->where($key, 'NOT LIKE', $value);
                continue;
            }

            $notValues[] = $value;
            continue;
        }

        if (count($notValues) > 0) {
            $this->builder->whereNotIn($key, $notValues);
        }
    }

    /**
     * @param string $splitValue
     * @return bool
     */
    protected function isNegated(string $splitValue): bool
    {
        return substr($splitValue, 0, 1) === self::NOT;
    }

    /**
     * @param $splitValue
     * @return bool
     */
    protected function hasWildCard(string $splitValue): bool
    {
        if (!$splitValue) {
            return false;
        };

        return $splitValue[0] === '%' || $splitValue[strlen($splitValue) - 1] === '%';
    }

    /**
     * @param $key
     * @param $values
     * @throws SearchException
     */
    public function lessThan($key, $values)
    {
        $this->lessOrMore($key, $values, '<');
    }

    /**
     * @param $key
     * @param $values
     * @throws SearchException
     */
    public function lessThanOrEqual($key, $values)
    {
        $this->lessOrMore($key, $values, '<=');
    }

    /**
     * @param $key
     * @param $values
     * @throws SearchException
     */
    public function greaterThan($key, $values)
    {
        $this->lessOrMore($key, $values, '>');
    }

    /**
     * @param $key
     * @param $values
     * @throws SearchException
     */
    public function greaterThanOrEqual($key, $values)
    {
        $this->lessOrMore($key, $values, '>=');
    }

    /**
     * @param $key
     * @param $values
     * @param $operator
     * @param $type
     * @throws SearchException
     */
    protected function lessOrMore($key, $values, $operator)
    {
        if (count($values) > 1) {
            throw new SearchException("[Search] Using $operator operator assumes one parameter only. Remove excess parameters.");
        }

        $this->builder->where($key, $operator, $values[0]);
    }

    /**
     * @param $key
     * @param $values
     * @throws SearchException
     */
    public function between($key, $values)
    {
        $this->betweenCallback($key, $values, '<>');
    }

    /**
     * @param $key
     * @param $values
     * @throws SearchException
     */
    public function notBetween($key, $values)
    {
        $this->betweenCallback($key, $values, '!<>');
    }

    /**
     * @param $key
     * @param $values
     * @param $operator
     * @throws SearchException
     */
    public function betweenCallback($key, $values, $operator)
    {
        if (count($values) != 2) {
            throw new SearchException("[Search] Using $operator operator assumes exactly 2 parameters. Wrong number of parameters provided.");
        }

        $callback = $operator == '<>' ? 'whereBetween' : 'whereNotBetween';

        $this->builder->{$callback}($key, [$values[0], $values[1]]);
    }
}
