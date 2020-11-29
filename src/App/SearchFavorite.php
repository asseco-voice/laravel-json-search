<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App;

use Illuminate\Database\Eloquent\Model;

class SearchFavorite extends Model
{
    protected $fillable = ['user_id', 'name', 'description', 'search'];
}
