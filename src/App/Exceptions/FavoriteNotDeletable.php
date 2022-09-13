<?php

namespace Asseco\JsonSearch\App\Exceptions;

use Asseco\JsonSearch\App\Models\SearchFavorite;
use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

class FavoriteNotDeletable extends Exception
{
    #[Pure] public function __construct(SearchFavorite $searchFavorite, int $code = 0, ?Throwable $previous = null)
    {
        $message = "Search favorite $searchFavorite->name is set as un-deletable. Flip the switch or abort deleting.";

        parent::__construct($message, $code, $previous);
    }

}