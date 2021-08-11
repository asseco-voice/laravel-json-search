<?php

namespace Illuminate\Database\Eloquent;

    class Model
    {
        public static function search(array $input): Builder
        {
            /** @var \Asseco\JsonSearch\SearchServiceProvider $instance */
            return $instance->boot();
        }
    }
