<?php

namespace Voice\SearchQueryBuilder\RequestParameters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\Traits\RemovesEmptyValues;

class Search
{
    use RemovesEmptyValues;

    /**
     * Constant by which values will be split within a single parameter. E.g. parameter=value1;value2
     */
    const VALUE_SEPARATOR = ';';
    public string $column;
    public array  $values;
    public string $type;
    private Model  $model;
    private string $searchParameter;
    private string $operator;

    /**
     * Search constructor.
     * @param Model $model
     * @param string $searchParameter
     * @param string $operator
     * @throws SearchException
     */
    public function __construct(Model $model, string $searchParameter, string $operator)
    {
        $this->model = $model;
        $this->searchParameter = $searchParameter;
        $this->operator = $operator;

        $this->parse();
    }

    /**
     * Exploding by a first occurrence of the operator to get the parameter key and value separated
     *
     * @throws SearchException
     */
    protected function parse()
    {
        $split = explode($this->operator, $this->searchParameter, 2);

        if (count($split) != 2) {
            throw new SearchException("[Search] Invalid search parameter(s): " . print_r($split, true));
        }

        $this->column = $split[0];
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
        $columns = $this->getModelColumns();

        if (!array_key_exists($column, $columns)) {
            throw new SearchException("[Search] Column $column is of unknown type, or it doesn't exist on a model.");
        }

        return $columns[$column];
    }

    /**
     * Will return column and column type array for a calling model.
     * Column types will equal Eloquent column types
     *
     * @return array
     */
    public function getModelColumns(): array
    {
        $table = $this->model->getTable();
        $columns = Schema::getColumnListing($table);

        $modelColumns = [];

        foreach ($columns as $column) {
            $modelColumns[$column] = DB::getSchemaBuilder()->getColumnType($table, $column);
        }

        return $modelColumns;
    }

}
