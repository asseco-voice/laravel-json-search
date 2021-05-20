<?php

namespace Illuminate\Database\Eloquent {

    class Model
    {
        public static function search(): \Closure
        {
            /** @var \Asseco\JsonSearch\SearchServiceProvider $instance */
            return $instance->search();
        }
    }
}
