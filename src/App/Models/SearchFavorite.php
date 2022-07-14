<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Models;

use Asseco\JsonSearch\Database\Factories\SearchFavoriteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchFavorite extends Model implements \Asseco\JsonSearch\App\Contracts\SearchFavorite
{
    use HasFactory;

    protected $fillable = ['model', 'name', 'owner_id', 'description', 'search'];

    protected $casts = [
        'search' => 'array',
    ];

    protected static function newFactory()
    {
        return SearchFavoriteFactory::new();
    }
}
