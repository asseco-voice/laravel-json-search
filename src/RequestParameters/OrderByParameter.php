<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\RequestParameters\Models\OrderBy;

class OrderByParameter extends AbstractParameter
{
    const ORDER_BY_DELIMITER = '=';

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
            $this->appendSingle($parameter);
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
        $parameter = $this->getParameterName();

        if ($this->request->has($parameter)) {
            return $this->getRawParameters($parameter);
        }

        return $this->configModel->getOrderBy();
    }

    /**
     * Append the 'order by' query from given parameters.
     *
     * @param string $orderByParameters
     * @throws SearchException
     */
    protected function appendSingle(string $orderByParameters): void
    {
        $order = $this->parseOrderByParameterValues($orderByParameters);

        $this->builder->orderBy($order->column(), $order->direction());
    }

    /**
     * Get order column and direction from provided parameter
     *
     * @param string $parameter
     * @return OrderBy
     * @throws SearchException
     */
    protected function parseOrderByParameterValues(string $parameter): OrderBy
    {
        $explodedParameters = explode(self::ORDER_BY_DELIMITER, $parameter, 2);
        $parameters = $this->removeEmptyValues($explodedParameters);

        return new OrderBy($parameters);
    }
}
