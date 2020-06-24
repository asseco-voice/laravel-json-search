<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Illuminate\Support\Facades\Config;
use Voice\SearchQueryBuilder\Exceptions\SearchException;

class Search extends AbstractParameter
{
    /**
     * Constant by which values will be split within a single attribute. E.g. attribute=value1;value2
     */
    const VALUE_SEPARATOR = ';';

    protected array $types = [
        '{b}'  => 'boolean',
        '{d}'  => 'date',
        '{t}'  => 'time',
        '{dt}' => 'datetime',
        '{n}'  => 'number',
    ];

    /**
     * Get name by which the attribute will be fetched
     * @return string
     */
    public function getAttributeName(): string
    {
        return 'search';
    }

    /**
     * Append the query to Eloquent builder
     * @throws SearchException
     */
    public function appendQuery(): void
    {
        $attributes = $this->parse();

        $this->builder->where(function () use ($attributes) {
            foreach ($attributes as $attribute) {
                $this->appendSearchQuery($attribute);
            }
        });
    }

    /**
     * Return key-value pairs array from query string attribute
     *
     * @return array
     * @throws SearchException
     */
    function parse(): array
    {
        if (!$this->request->has($this->getAttributeName())) {
            throw new SearchException("[Search] Couldn't match anything for '" . $this->getAttributeName() . "' query string.");
        }

        return $this->getRawAttributes($this->getAttributeName());
    }

    /**
     * Append the query based on the given attributes
     *
     * @param $searchAttribute
     * @throws SearchException
     */
    protected function appendSearchQuery(string $searchAttribute): void
    {
        [$operator, $callback] = $this->operatorCallbacks->parseOperatorAndCallback($searchAttribute);
        [$column, $values, $type] = $this->parseSearchAttributeValues($searchAttribute, $operator);

        $this->checkForbidden($column);

        $splitValues = $this->splitValues($searchAttribute, $values);

        call_user_func($callback, $column, $splitValues, $type);
    }

    /**
     * Exploding by a first occurrence of the operator to get the attribute key and value separated
     *
     * @param $searchAttribute
     * @param $operator
     * @return array
     * @throws SearchException
     */
    protected function parseSearchAttributeValues($searchAttribute, $operator): array
    {
        $split = explode($operator, $searchAttribute, 2);

        if (count($split) != 2) {
            throw new SearchException("[Search] Invalid search attribute(s): " . print_r($split, true));
        }

        $attribute = $split[0];
        $values = $split[1];

        [$type, $attribute] = $this->getAttributeType($attribute);

        return [$attribute, $values, $type];
    }

    /**
     * Check if global forbidden key is used
     *
     * @param string $attribute
     * @throws SearchException
     */
    protected function checkForbidden(string $attribute)
    {
        $forbiddenKeys = Config::get('asseco-voice.search.globalForbiddenColumns');
        $forbiddenKeys = $this->searchModel->getForbidden($forbiddenKeys);

        if (in_array($attribute, $forbiddenKeys)) {
            throw new SearchException("[Search] Searching by '$attribute' field is forbidden. Check the configuration if this is not a desirable behavior.");
        }
    }

    /**
     * Check for attribute type (will be applied to all values)
     *
     * @param $attribute
     * @return array
     */
    protected function getAttributeType($attribute): array
    {
        $type = null;

        foreach ($this->types as $typeKey => $name) {
            if (strpos($attribute, $typeKey) !== false) {
                $type = $name;
                $attribute = str_replace($typeKey, '', $attribute);
                break;
            }
        }

        return [$type, $attribute];
    }

    /**
     * Split values by a given separator
     *
     * Input: val1;val2
     *
     * Output: val1
     *         val2
     *
     * @param $attribute
     * @param $values
     * @return array
     * @throws SearchException
     */
    protected function splitValues(string $attribute, string $values): array
    {
        $valueArray = explode(self::VALUE_SEPARATOR, $values);
        $cleanedUpValues = $this->removeEmptyValues($valueArray);

        if (count($cleanedUpValues) < 1) {
            throw new SearchException("[Search] Attribute $attribute is missing a value.");
        }

        return $cleanedUpValues;
    }

}
