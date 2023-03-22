<?php

namespace Illuminate\Database\Eloquent;

    class Model
    {
        public static function jsonSearch(array $input): Builder
        {
            /** @var \Asseco\JsonSearch\SearchServiceProvider $instance */
            return $instance->boot();
        }
    }
