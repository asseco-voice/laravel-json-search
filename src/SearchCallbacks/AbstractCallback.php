<?php

namespace Voice\SearchQueryBuilder\SearchCallbacks;

use Illuminate\Database\Eloquent\Builder;
use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\RequestParameters\Models\Search;

abstract class AbstractCallback
{
    /**
     * Constant declaring what will be used as a negation parameter.
     */
    const NOT = '!';
    const LIKE = '*';

    protected Builder $builder;
    protected Search  $searchModel;

    public function __construct(Builder $builder, Search $searchModel)
    {
        $this->builder = $builder;
        $this->searchModel = $searchModel;
    }

    /**
     * Child class MUST extend a NAME constant.
     * This is a Laravel friendly name for columns based on Laravel migration column types
     *
     * @return string
     */
    public static function getCallbackOperator(): string
    {
        return static::OPERATOR;
    }

    /**
     * Execute a callback on a given column, providing the array of values
     * @throws SearchException
     */
    abstract public function execute(): void;

    /**
     * TODO: move to a separate class
     *
     * @param $key
     * @param $values
     * @param $operator
     * @param $type
     * @throws SearchException
     */
    public function lessOrMore($key, $values, $operator)
    {
        if (count($values) > 1) {
            throw new SearchException("[Search] Using $operator operator assumes one parameter only. Remove excess parameters.");
        }

        $this->builder->where($key, $operator, $values[0]);
    }

    /**
     * TODO: move to a separate class
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

    /**
     * @param string $splitValue
     * @return bool
     */
    protected function isNegated(string $splitValue): bool
    {
        return substr($splitValue, 0, 1) === self::NOT;
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function hasWildCard(string $value): bool
    {
        if (!$value) {
            return false;
        };

        return $value[0] === self::LIKE || $value[strlen($value) - 1] === self::LIKE;
    }

    protected function replaceWildCard($value)
    {
        return str_replace(self::LIKE, '%', $value);
    }
}
