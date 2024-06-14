<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Models;

use Asseco\JsonSearch\App\Exceptions\FavoriteNotDeletable;
use Asseco\JsonSearch\Database\Factories\SearchFavoriteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchFavorite extends Model implements \Asseco\JsonSearch\App\Contracts\SearchFavorite
{
    use HasFactory;

    protected $fillable = ['model', 'name', 'owner_id', 'description', 'search', 'deletable'];

    protected $casts = [
        'search' => 'array',
        'deletable' => 'boolean',
    ];

    protected static function newFactory()
    {
        return SearchFavoriteFactory::new();
    }

    protected static function booted()
    {
        static::deleting(function (self $searchFavorite) {
            if (!$searchFavorite->deletable) {
                throw new FavoriteNotDeletable($searchFavorite);
            }
        });
    }
}
