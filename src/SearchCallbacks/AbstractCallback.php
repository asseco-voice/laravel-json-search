<?php

namespace Voice\SearchQueryBuilder\SearchCallbacks;

use Illuminate\Database\Eloquent\Builder;
use Voice\SearchQueryBuilder\CategorizedValues;
use Voice\SearchQueryBuilder\Config\OperatorsConfig;
use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\RequestParameters\Models\Search;

abstract class AbstractCallback
{
    protected Builder           $builder;
    protected Search            $searchModel;
    protected CategorizedValues $categorizedValues;
    protected OperatorsConfig   $operatorsConfig;

    /**
     * AbstractCallback constructor.
     * @param Builder $builder
     * @param Search $searchModel
     * @param OperatorsConfig $operatorsConfig
     * @throws SearchException
     */
    public function __construct(Builder $builder, Search $searchModel, OperatorsConfig $operatorsConfig)
    {
        $this->builder = $builder;
        $this->searchModel = $searchModel;
        $this->operatorsConfig = $operatorsConfig;

        $this->categorizedValues = new CategorizedValues($operatorsConfig, $this->searchModel);

        $this->builder->when(
            str_contains($this->searchModel->column, '.'),
            function (Builder $builder) {
                $this->appendRelations($builder, $this->searchModel->column, $this->categorizedValues);
            },
            function (Builder $builder) {
                $this->execute($builder, $this->searchModel->column, $this->categorizedValues);
            }
        );
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
     * @param Builder $builder
     * @param string $column
     * @param CategorizedValues $values
     */
    abstract public function execute(Builder $builder, string $column, CategorizedValues $values): void;

    public function appendRelations(Builder $builder, string $column, CategorizedValues $values): void
    {
        [$relationName, $relatedColumn] = explode('.', $column);

        $builder->orWhereHas($relationName, function (Builder $builder) use ($relatedColumn, $values) {
            $this->execute($builder, $relatedColumn, $values);
        });
    }

    /**
     * @param Builder $builder
     * @param $key
     * @param $values
     * @param $operator
     * @throws SearchException
     */
    protected function lessOrMoreCallback(Builder $builder, $key, $values, $operator)
    {
        if (count($values) > 1) {
            throw new SearchException("[Search] Using $operator operator assumes one parameter only. Remove excess parameters.");
        }

        $builder->where($key, $operator, $values[0]);
    }

    /**
     * @param Builder $builder
     * @param $key
     * @param $values
     * @param $operator
     * @throws SearchException
     */
    protected function betweenCallback(Builder $builder, $key, $values, $operator)
    {
        if (count($values) != 2) {
            throw new SearchException("[Search] Using $operator operator assumes exactly 2 parameters. Wrong number of parameters provided.");
        }

        $callback = $operator == '<>' ? 'whereBetween' : 'whereNotBetween';

        $builder->{$callback}($key, [$values[0], $values[1]]);
    }
}
