<?php

namespace Voice\SearchQueryBuilder;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Voice\SearchQueryBuilder\Exceptions\SearchException;

class Searcher
{
    /**
     * Constant by which attributes will be split. E.g. attribute=value\attribute2=value2
     */
    const SEARCH_PARAMETER_SEPARATOR = '\\';
    /**
     * Constant by which values will be split within a single attribute. E.g. attribute=value1;value2
     */
    const VALUE_SEPARATOR = ';';

    const RETURN_QUERY = 'returns';
    const SEARCH_QUERY = 'search';
    const ORDER_BY_QUERY = 'order-by';

    private Builder           $query;
    private OperatorCallbacks $operatorCallbacks;

    protected array $types = [
        '{b}'  => 'boolean',
        '{d}'  => 'date',
        '{t}'  => 'time',
        '{dt}' => 'datetime',
        '{n}'  => 'number',
    ];

    /*
     * TODO:
     * exclude attributes -> u configu. Da/ne?
     * order -> u configu
     * ...ako uvrstiš u config ono za return, šta to znači ako proslijediš krivi parametar? treba exception baciti
     * a neće znati kako
     *
     * datum od danas toliko dana
     * is null is not null
     * bool isprobaj 1/0
     * relacije
     *
     * limit
     * paginacija
     *
     * forsiraj type -> riješi tipove one
     *
     * or?
     *
     * https://freek.dev/1182-searching-models-using-a-where-like-query-in-laravel
     *
     */

    /**
     * Searcher constructor.
     * @param Builder $query
     * @param Request $request
     * @throws Exception
     */
    public function __construct(Builder $query, Request $request)
    {
        $this->query = $query;
        $this->registerCallbacks($query);
        $this->search($request);
    }

    protected function registerCallbacks(Builder $query)
    {
        $this->operatorCallbacks = new OperatorCallbacks($query);
    }

    /**
     * @param $request
     * @throws Exception
     */
    protected function search(Request $request)
    {
        $search = $this->parseSearchAttributes($request);
        $returns = $this->parseReturnAttributes($request);
        $orderBy = $this->parseOrderByAttributes($request);

        $this->appendQueries($search, $returns, $orderBy);
        Log::info('[Search] SQL: ' . $this->query->toSql());
    }

    /**
     * Take input string, match it, and explode 'search' attributes using attribute separator
     *
     * Input: (key=value\key2=value2)
     *
     * Output: key=value
     *         key2=value2
     *
     * @param Request $request
     * @return array
     * @throws SearchException
     */
    protected function parseSearchAttributes(Request $request): array
    {
        if (!$request->has(self::SEARCH_QUERY)) {
            throw new SearchException("[Search] Couldn't match anything for '" . self::SEARCH_QUERY . "' query string.");
        }

        return $this->getAttributes($request, self::SEARCH_QUERY, self::SEARCH_PARAMETER_SEPARATOR);
    }

    /**
     * Take input string, match it, and explode 'returns' attribute using value separator
     *
     * Input: (attribute1;attribute2)
     *
     * Output: attribute1
     *         attribute2
     *
     * @param Request $request
     * @return array
     * @throws SearchException
     */
    protected function parseReturnAttributes(Request $request): array
    {
        return $request->has(self::RETURN_QUERY) ?
            $this->getAttributes($request, self::RETURN_QUERY, self::VALUE_SEPARATOR) :
            ['*'];
    }

    /**
     * Take input string, match it, and explode 'order by' attributes using attribute separator
     *
     * Input: (key=value\key2=value2)
     *
     * Output: key=value
     *         key2=value2
     *
     * @param Request $request
     * @return array
     * @throws SearchException
     */
    protected function parseOrderByAttributes(Request $request): array
    {
        return $request->has(self::ORDER_BY_QUERY) ?
            $this->getAttributes($request, self::ORDER_BY_QUERY, self::SEARCH_PARAMETER_SEPARATOR) :
            [];
    }

    /**
     * Extract raw string from parenthesis provided in the query string.
     *
     * @param Request $request
     * @param string $inputType
     * @param string $separator
     * @return mixed
     * @throws SearchException
     */
    protected function getAttributes(Request $request, string $inputType, string $separator): array
    {
        $input = $request->query($inputType);

        preg_match('/\((.*?)\)/', $input, $matched);

        if (count($matched) < 2) {
            throw new SearchException("[Search] Couldn't match anything for '$inputType' query string. Input found: $input. Are you missing a parenthesis?");
        }

        $attributes = explode($separator, $matched[1]);
        $cleanedUpAttributes = $this->removeEmptyValues($attributes);

        if (count($cleanedUpAttributes) < 1) {
            throw new SearchException("[Search] Couldn't match attributes for '$inputType' query string. Input found: $input. Did you include anything within parenthesis?");
        }

        return $cleanedUpAttributes;
    }

    /**
     * Append all queries based on input parameters
     *
     * @param array $returns
     * @param array $search
     * @param array $orderBy
     * @throws SearchException
     */
    protected function appendQueries(array $search, array $returns, array $orderBy): void
    {
        $this->query->select($returns)->where(function () use ($search) {
            foreach ($search as $searchAttribute) {
                $this->appendSearchQuery($searchAttribute);
            }
        });

        foreach ($orderBy as $orderByAttribute) {
            $this->appendOrderBy($orderByAttribute);
        }
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
        [$attribute, $values, $type] = $this->parseSearchAttributeValues($searchAttribute, $operator);

        $splitValues = $this->splitValues($searchAttribute, $values);

        call_user_func($callback, $attribute, $splitValues, $type);
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

        $this->query->orderBy($column, $direction);
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

    /**
     * Remove empty values from a given array
     *
     * @param array $input
     * @return array
     */
    protected function removeEmptyValues(array $input): array
    {
        $trimmedInput = array_map('trim', $input);

        $deleteKeys = array_keys(array_filter($trimmedInput, function ($item) {
            return $item == '';
        }));

        foreach ($deleteKeys as $deleteKey) {
            unset($trimmedInput[$deleteKey]);
        }

        return $trimmedInput;
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
