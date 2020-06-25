<?php

namespace Voice\SearchQueryBuilder\RequestParameters\Models;

use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\RequestParameters\Traits\RemovesEmptyValues;

class Search
{
    use RemovesEmptyValues;

    /**
     * Constant by which values will be split within a single parameter. E.g. parameter=value1;value2
     */
    const VALUE_SEPARATOR = ';';

    private $searchParameter;
    private $operator;

    public string $column;
    public array  $values;
    public string $type;

    /**
     * Search constructor.
     * @param string $searchParameter
     * @param string $operator
     * @throws SearchException
     */
    public function __construct(string $searchParameter, string $operator)
    {
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
        $this->type = '';
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

}
