<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Exceptions\SearchException;

class OrderBy extends AbstractParameter
{
    /**
     * Get name by which the attribute will be fetched
     * @return string
     */
    public function getAttributeName(): string
    {
        return 'order-by';
    }

    /**
     * Append the query to Eloquent builder
     * @throws SearchException
     */
    public function appendQuery(): void
    {
        $attributes = $this->parse();

        foreach ($attributes as $attribute) {
            $this->appendOrderBy($attribute);
        }
    }

    /**
     * Return key-value pairs array from query string attribute
     *
     * @return array
     * @throws SearchException
     */
    public function parse(): array
    {
        if ($this->request->has($this->getAttributeName())) {
            return $this->getRawAttributes($this->getAttributeName());
        }

        return $this->searchModel->getOrderBy();
    }

    /**
     * Append the 'order by' query from given attributes.
     *
     * @param string $orderByAttributes
     * @throws SearchException
     */
    protected function appendOrderBy(string $orderByAttributes): void
    {
        [$column, $direction] = $this->parseOrderByAttributeValues($orderByAttributes);

        $this->builder->orderBy($column, $direction);
    }


    /**
     * Get order column and direction from provided attribute
     *
     * @param string $attribute
     * @return array
     * @throws SearchException
     */
    protected function parseOrderByAttributeValues(string $attribute): array
    {
        $exploded = explode('=', $attribute, 2);
        $split = $this->removeEmptyValues($exploded);

        if (count($split) == 0) {
            throw new SearchException("[Search] Something went wrong with ordering: $attribute.");
        }

        $column = $split[0];
        $direction = 'asc';

        if (count($split) > 1) {
            $direction = $split[1];
        }

        return [$column, $direction];
    }

}
