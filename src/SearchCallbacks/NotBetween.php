<?php

namespace Voice\SearchQueryBuilder\SearchCallbacks;

use Voice\SearchQueryBuilder\Exceptions\SearchException;

class NotBetween extends AbstractCallback
{
    const OPERATOR = '!<>';

    /**
     * Execute a callback on a given column, providing the array of values
     *
     * @throws SearchException
     */
    public function execute(): void
    {
        $this->betweenCallback($this->searchModel->column, $this->searchModel->values, '!<>');
    }
}
