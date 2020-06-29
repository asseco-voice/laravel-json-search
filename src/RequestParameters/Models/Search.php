<?php

namespace Voice\SearchQueryBuilder\RequestParameters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Voice\SearchQueryBuilder\Config\ModelConfig;
use Voice\SearchQueryBuilder\Config\OperatorsConfig;
use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\Traits\RemovesEmptyValues;

class Search
{
    use RemovesEmptyValues;

    /**
     * Constant by which values will be split within a single parameter. E.g. parameter=value1;value2
     */
    const VALUE_SEPARATOR = ';';

    public string  $column;
    public array   $values;
    public string  $type;
    public string  $operator;

    private Model       $model;
    private string      $argument;
    private ModelConfig $modelConfig;

    /**
     * Search constructor.
     * @param Model $model
     * @param ModelConfig $modelConfig
     * @param string $argument
     * @throws SearchException
     */
    public function __construct(Model $model, ModelConfig $modelConfig, string $argument)
    {
        $this->model = $model;
        $this->argument = $argument;
        $this->modelConfig = $modelConfig;

        $operatorConfig = new OperatorsConfig();
        $operators = $operatorConfig->getOperators();

        foreach ($operators as $operator) {
            $argumentHasOperator = strpos($argument, $operator) !== false;
            if (!$argumentHasOperator) {
                continue;
            }

            $this->operator = $operator;
            $this->parse();
            return;
        }

        throw new SearchException("[Search] No valid callback registered for $argument. Are you missing an operator?");
    }

    /**
     * Exploding by a first occurrence of the operator to get the parameter key and value separated
     *
     * @throws SearchException
     */
    protected function parse()
    {
        $split = explode($this->operator, $this->argument, 2);

        if (count($split) != 2) {
            throw new SearchException("[Search] Invalid search parameter(s): " . print_r($split, true));
        }

        $this->column = $split[0];
        $this->checkForbidden($this->column);

        $this->values = $this->splitValues($this->column, $split[1]);
        $this->type = $this->getColumnType($this->column);
    }

    /**
     * Split values by a given separator
     *
     * Input: val1;val2
     *
     * Output: val1
     *         val2
     *
     * @param $parameter
     * @param $values
     * @return array
     * @throws SearchException
     */
    protected function splitValues(string $parameter, string $values): array
    {
        $valueArray = explode(self::VALUE_SEPARATOR, $values);
        $cleanedUpValues = $this->removeEmptyValues($valueArray);

        if (count($cleanedUpValues) < 1) {
            throw new SearchException("[Search] Parameter $parameter is missing a value.");
        }

        return $cleanedUpValues;
    }

    /**
     * @param $column
     * @return string
     * @throws SearchException
     */
    public function getColumnType($column): string
    {
        $columns = $this->modelConfig->getModelColumns();

        if (!array_key_exists($column, $columns)) {
            // TODO: integrate recursive column check for related models?
            return 'generic';
        }

        return $columns[$column];
    }

    /**
     * Check if global forbidden key is used
     *
     * @param string $parameter
     * @throws SearchException
     */
    protected function checkForbidden(string $parameter)
    {
        $forbiddenKeys = Config::get('asseco-voice.search.globalForbiddenColumns');
        $forbiddenKeys = $this->modelConfig->getForbidden($forbiddenKeys);

        if (in_array($parameter, $forbiddenKeys)) {
            throw new SearchException("[Search] Searching by '$parameter' field is forbidden. Check the configuration if this is not a desirable behavior.");
        }
    }
}
