<?php

return [
    'search' => [
        /**
         * List of globally forbidden columns to search on.
         * Searching by forbidden columns will throw an exception
         * This takes precedence before other exclusions.
         */
        'globalForbiddenColumns' => [
            // 'id', 'created_at' ...
        ],

        /**
         * Refined options for a single model.
         * Use if you want to enforce rules on a specific model without affecting globally all models
         */
        'modelOptions'           => [

            /**
             * For real usage, use real models without quotes. This is only meant to show the available options.
             */
            'SomeModel::class' => [
                /**
                 * If enabled, this will read from model guarded/fillable properties
                 * and decide whether it is allowed to search by these parameters.
                 * This takes precedence before forbidden columns, but if both is used, it
                 * will behave like union of columns to be excluded.
                 * Searching on forbidden columns will throw an exception.
                 */
                'eloquentExclusion' => false,
                /**
                 * Disable search on specific columns. Searching on forbidden columns will throw an exception
                 */
                'forbiddenColumns'  => ['attribute', 'attribute2'],
                /**
                 * Array of attributes to order by in 'column => direction' format.
                 * 'order-by' from query string takes precedence before these values.
                 */
                'orderBy'           => [
                    'id'         => 'asc',
                    'created_at' => 'desc'
                ],
                /**
                 * List of attributes to return. Return values forwarded within the request will
                 * override these values. This acts as a 'SELECT /return only attributes/' from.
                 * By default, 'SELECT *' will be ran.
                 */
                'returns'           => ['attribute', 'attribute2'],



                /**
                 * TBD
                 * Some attributes may be different on frontend than on backend.
                 * It is possible to map such attributes so that the true ORM
                 * property stays hidden.
                 */
                'attributeMapping'  => [
                    'frontendAttribute' => 'backendAttribute',
                ],
            ],
        ],

    ]
];
