<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

class LimitParameter extends AbstractParameter
{
    public function getParameterName(): string
    {
        return 'limit';
    }

    public function appendQuery(): void
    {
        $value = $this->getParameterValue();

        if ($value) {
            $this->builder->limit($value);
        }
    }
}
