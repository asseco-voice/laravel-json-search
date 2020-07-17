<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Illuminate\Support\Facades\DB;

class CountParameter extends AbstractParameter
{

    public function getParameterName(): string
    {
        return 'count';
    }

    public function appendQuery(): void
    {
        if ($this->request->has($this->getParameterName())) {
            $this->builder->select(DB::raw('count(*) as count'));
        }
    }
}
