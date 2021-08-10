<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\Database\Seeders;

use Asseco\JsonSearch\App\Contracts\SearchFavorite;
use Illuminate\Database\Seeder;

class SearchFavoriteSeeder extends Seeder
{
    public function run(): void
    {
        /** @var SearchFavorite $favorite */
        $favorite = app(SearchFavorite::class);

        if (config('app.env') !== 'production') {
            $favorite::factory()->count(50)->create();
        }
    }
}
