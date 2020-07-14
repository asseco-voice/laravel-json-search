<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\RequestParameters\Models\OrderBy;

class OrderByParameter extends AbstractParameter
{
    const VALUE_DELIMITER = '=';

    public function getParameterName(): string
    {
        return 'order-by';
    }

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

    protected function fetchAlternative(): array
    {
        return $this->modelConfig->getOrderBy();
    }
}
