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
        'search'    => 'array',
        'deletable' => 'boolean',
    ];

    protected static function newFactory()
    {
        return SearchFavoriteFactory::new();
    }

    protected static function booted()
    {
        static::saving(function (self $searchFavorite) {
            if ($this->isDirty(['name', 'owner_id']) && $searchFavorite->exists()) {
                throw new \Exception("Favorite with name $searchFavorite->name already exists for user.");
            }
        });

        static::deleting(function (self $searchFavorite) {
            if (!$searchFavorite->deletable) {
                throw new FavoriteNotDeletable($searchFavorite);
            }
        });
    }

    protected function exists(): bool
    {
        return app(\Asseco\JsonSearch\App\Contracts\SearchFavorite::class)::query()
            ->where('name', $this->name)
            ->where('owner_id', $this->owner_id)
            ->exists();
    }
}
