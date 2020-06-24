<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Exceptions\SearchException;

class OrderBy extends AbstractParameter
{
    /**
     * Get name by which the parameter will be fetched
     * @return string
     */
    public function getParameterName(): string
    {
        return 'order-by';
    }

    /**
     * Append the query to Eloquent builder
     * @throws SearchException
     */
    public function appendQuery(): void
    {
        $parameters = $this->parse();

        foreach ($parameters as $parameter) {
            $this->appendOrderBy($parameter);
        }
    }

    /**
     * Return key-value pairs array from query string parameter
     *
     * @return array
     * @throws SearchException
     */
    public function parse(): array
    {
        if ($this->request->has($this->getParameterName())) {
            return $this->getRawParameters($this->getParameterName());
        }

        return $this->searchModel->getOrderBy();
    }

    /**
     * Append the 'order by' query from given parameters.
     *
     * @param string $orderByParameters
     * @throws SearchException
     */
    protected function appendOrderBy(string $orderByParameters): void
    {
        [$column, $direction] = $this->parseOrderByParameterValues($orderByParameters);

        $this->builder->orderBy($column, $direction);
    }


    /**
     * Get order column and direction from provided parameter
     *
     * @param string $parameter
     * @return array
     * @throws SearchException
     */
    protected function parseOrderByParameterValues(string $parameter): array
    {
        $exploded = explode('=', $parameter, 2);
        $split = $this->removeEmptyValues($exploded);

        if (count($split) == 0) {
            throw new SearchException("[Search] Something went wrong with ordering: $parameter.");
        }

        $column = $split[0];
        $direction = 'asc';

        if (count($split) > 1) {
            $direction = $split[1];
        }

        return [$column, $direction];
    }

}
