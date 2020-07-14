<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

class OffsetParameter extends AbstractParameter
{
    public function getParameterName(): string
    {
        return 'offset';
    }

    public function appendQuery(): void
    {
        $value = $this->getParameterValue();

        if ($value) {
            $this->builder->offset($value);
        }
    }
}
