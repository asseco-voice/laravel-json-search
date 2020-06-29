<?php

namespace Voice\SearchQueryBuilder\Config;

use Voice\SearchQueryBuilder\Types\AbstractType;
use Voice\SearchQueryBuilder\Types\GenericType;

class TypesConfig extends SearchConfig
{
    const CONFIG_KEY = 'types';

    public function instantiateType(string $type): AbstractType
    {
        if (!array_key_exists($type, $this->registered)) {
            return new GenericType();
        }

        return new $this->registered[$type];
    }
}
