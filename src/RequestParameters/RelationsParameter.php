<?php

namespace Voice\SearchQueryBuilder\RequestParameters;

use Voice\SearchQueryBuilder\Exceptions\SearchException;

class RelationsParameter extends AbstractParameter
{
    /**
     * Get name by which the parameter will be fetched
     * @return string
     */
    public function getParameterName(): string
    {
        return 'relations';
    }

    /**
     * Append the query to Eloquent builder
     * @throws SearchException
     */
    public function appendQuery(): void
    {
        $arguments = $this->getArguments();

        $this->builder->with($arguments);
    }

    /**
     * Provide additional method as a fallback if query string argument is not present.
     * Empty array is a valid default, meaning no fallback is available.
     * Override if fallback is needed.
     *
     * @return array
     */
    protected function fetchAlternative(): array
    {
        return $this->configModel->getRelations();
    }
}
