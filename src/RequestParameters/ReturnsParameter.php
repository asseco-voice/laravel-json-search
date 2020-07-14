<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Exceptions\SearchException;

class ReturnsParameter extends AbstractParameter
{
    public function getParameterName(): string
    {
        return 'returns';
    }

    public function appendQuery(): void
    {
        $arguments = $this->getArguments();

        $this->builder->select($arguments);
    }

    protected function fetchAlternative(): array
    {
        return $this->modelConfig->getReturns();
    }
}
