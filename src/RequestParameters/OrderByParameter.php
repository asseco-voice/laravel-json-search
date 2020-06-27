<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\RequestParameters\Models\OrderBy;

class OrderByParameter extends AbstractParameter
{
    const VALUE_DELIMITER = '=';

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
        $arguments = $this->getArguments();

        foreach ($arguments as $argument) {
            $this->appendSingle($argument);
        }
    }

    /**
     * Append the 'order by' query from given arguments.
     *
     * @param string $argument
     * @throws SearchException
     */
    protected function appendSingle(string $argument): void
    {
        $order = $this->parseArgument($argument);

        $this->builder->orderBy($order->column(), $order->direction());
    }

    /**
     * Get column and direction from provided argument
     *
     * @param string $argument
     * @return OrderBy
     * @throws SearchException
     */
    protected function parseArgument(string $argument): OrderBy
    {
        $splitArgument = explode(self::VALUE_DELIMITER, $argument, 2);
        $splitArgument = $this->removeEmptyValues($splitArgument);

        return new OrderBy($splitArgument);
    }

    /**
     * Provide additional method as a fallback if query string argument is not present.
     * Empty array is a valid default, meaning no fallback is available.
     * Override if fallback is needed.
     *
     * @return array
     */
    protected function fetchAlternative(): array
    {
        return $this->configModel->getOrderBy();
    }
}
