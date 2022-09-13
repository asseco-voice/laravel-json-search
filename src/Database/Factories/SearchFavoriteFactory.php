<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SearchFavoriteFactory extends Factory
{
    public function modelName()
    {
        return config('asseco-search.models.search_favorite');
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'        => $this->faker->unique()->word,
            'owner_id'    => $this->faker->uuid,
            'description' => $this->faker->sentence,
            'search'      => json_encode(['test' => 'test']),
            'deletable'   => $this->faker->boolean(90),
            'created_at'  => $this->faker->dateTime(),
            'updated_at'  => $this->faker->dateTime(),
        ];
    }
}
