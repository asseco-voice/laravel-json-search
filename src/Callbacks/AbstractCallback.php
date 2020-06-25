<?php

namespace Voice\SearchQueryBuilder\Callbacks;

use Illuminate\Database\Eloquent\Builder;
use Voice\SearchQueryBuilder\Exceptions\SearchException;

abstract class AbstractCallback
{
    /**
     * Constant declaring what will be used as a negation parameter.
     */
    const NOT = '!';
    const LIKE = '$';

    public $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Execute a callback on a given column, providing the array of values
     *
     * @param string $column
     * @param array $values
     * @param string $type
     */
    abstract function execute(string $column, array $values, string $type);

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
}
