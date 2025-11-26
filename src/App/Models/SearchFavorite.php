<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Models;

use Asseco\JsonSearch\App\Exceptions\FavoriteNotDeletable;
use Asseco\JsonSearch\Database\Factories\SearchFavoriteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $model
 * @property string $group
 * @property string $owner_id
 * @property string $name
 * @property string $description
 * @property array $search
 * @property boolean $deletable
  */
class SearchFavorite extends Model implements \Asseco\JsonSearch\App\Contracts\SearchFavorite
{
    use HasFactory;

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'search'    => 'array',
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
