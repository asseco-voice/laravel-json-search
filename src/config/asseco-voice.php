<?php

use Voice\SearchQueryBuilder\Callbacks\Between;
use Voice\SearchQueryBuilder\Callbacks\Equals;
use Voice\SearchQueryBuilder\Callbacks\GreaterThan;
use Voice\SearchQueryBuilder\Callbacks\GreaterThanOrEqual;
use Voice\SearchQueryBuilder\Callbacks\LessThan;
use Voice\SearchQueryBuilder\Callbacks\LessThanOrEqual;
use Voice\SearchQueryBuilder\Callbacks\NotBetween;
use Voice\SearchQueryBuilder\Callbacks\NotEquals;
use Voice\SearchQueryBuilder\RequestParameters\OrderByParameter;
use Voice\SearchQueryBuilder\RequestParameters\RelationsParameter;
use Voice\SearchQueryBuilder\RequestParameters\ReturnsParameter;
use Voice\SearchQueryBuilder\RequestParameters\SearchParameter;

return [
    'search' => [

        'registeredRequestParameters' => [
            SearchParameter::class,
            ReturnsParameter::class,
            OrderByParameter::class,
            RelationsParameter::class
        ],

        /**
         * Registered operators and callbacks they use. Order matters!
         * Operators with more characters must come before those with less.
         */
        'registeredSearchCallbacks'   => [
            '!<>' => NotBetween::class,
            '<='  => LessThanOrEqual::class,
            '>='  => GreaterThanOrEqual::class,
            '<>'  => Between::class,
            '!='  => NotEquals::class,
            '='   => Equals::class,
            '<'   => LessThan::class,
            '>'   => GreaterThan::class,
        ],

        /**
         * List of globally forbidden columns to search on.
         * Searching by forbidden columns will throw an exception
         * This takes precedence before other exclusions.
         */
        'globalForbiddenColumns'      => [
            // 'id', 'created_at' ...
        ],

        /**
         * Refined options for a single model.
         * Use if you want to enforce rules on a specific model without affecting globally all models
         */
        'modelOptions'                => [

            /**
             * For real usage, use real models without quotes. This is only meant to show the available options.
             */
            'SomeModel::class' => [
                /**
                 * If enabled, this will read from model guarded/fillable properties
                 * and decide whether it is allowed to search by these parameters.
                 * If guarded property is present, fillable won't be taken. Laravel standard
                 * is to use one or the other, not both.
                 * This takes precedence before forbidden columns, but if both are used, it
                 * will behave like union of columns to be excluded.
                 * Searching on forbidden columns will throw an exception.
                 */
                'eloquentExclusion' => false,
                /**
                 * Disable search on specific columns. Searching on forbidden columns will throw an exception
                 */
                'forbiddenColumns'  => ['column', 'column2'],
                /**
                 * Array of columns to order by in 'column => direction' format.
                 * 'order-by' from query string takes precedence before these values.
                 */
                'orderBy'           => [
                    'id'         => 'asc',
                    'created_at' => 'desc'
                ],
                /**
                 * List of columns to return. Return values forwarded within the request will
                 * override these values. This acts as a 'SELECT /return only columns/' from.
                 * By default, 'SELECT *' will be ran.
                 */
                'returns'           => ['column', 'column2'],
                /**
                 * List of relations to load by default. These will be overridden if provided within query string.
                 */
                'relations'         => ['rel1', 'rel2'],

                /**
                 * TBD
                 * Some column names may be different on frontend than on backend.
                 * It is possible to map such columns so that the true ORM
                 * property stays hidden.
                 */
                'columnMapping'     => [
                    'frontendColumn' => 'backendColumn',
                ],
            ],
        ],

    ]
];
