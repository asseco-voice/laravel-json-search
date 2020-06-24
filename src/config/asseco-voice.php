<?php

return [
    'search' => [
        /**
         * List of attributes which are to be excluded on all models.
         * This takes precedence before other exclusions.
         */
        'globalForbiddenAttributes' => [
            // 'id', 'created_at' ...
        ],
        'modelOptions'            => [
            /**
             * For real usage, use real models without quotes. This is meant to show the available options.
             */
            'SomeModel::class' => [
                /**
                 * If enabled, this will read from model guarded/fillable properties
                 * and decide whether it is possible to search by these parameters.
                 * This takes precedence before attributes, but if both is used, it
                 * will behave like union of attributes to be excluded.
                 */
                'eloquentExclusion' => false,
                /**
                 * Excluding search by specific attributes. If search parameter is
                 * used, but is on excluded list, it will be ignored.
                 */
                'excludeAttributes' => ['attribute', 'attribute2'],
                /**
                 * Array of attributes to order by. Order forwarded within the request will
                 * override these values.
                 */
                'orderBy'           => ['id', 'created_at'],
                /**
                 * Some attributes may be different on frontend than on backend.
                 * It is possible to map such attributes so that the true ORM
                 * property stays hidden.
                 */
                'attributeMapping'  => [
                    'frontendAttribute' => 'backendAttribute',
                ],
                /**
                 * List of attributes to return. Return values forwarded within the request will
                 * override these values. This acts as a 'SELECT /return only attributes/' from.
                 * By default, 'SELECT *' will be ran.
                 */
                'returnOnly'        => ['attribute', 'attribute2'],
            ],
        ],

    ]
];
