<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Exceptions\SearchException;

class RelationsParameter extends AbstractParameter
{
    public function getParameterName(): string
    {
        return 'relations';
    }

    public function appendQuery(): void
    {
        $arguments = $this->getArguments();

        $this->builder->with($arguments);
    }

    protected function fetchAlternative(): array
    {
        return $this->modelConfig->getRelations();
    }
}
