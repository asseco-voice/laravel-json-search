<?php

namespace Illuminate\Database\Eloquent;

    class Model
    {
        public static function search(): \Closure
        {
            /** @see \Asseco\JsonSearch\SearchServiceProvider */
            /** @var \Asseco\JsonQueryBuilder\JsonQuery $instance */
            return $instance->search();
        }
    }
