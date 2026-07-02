<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'movie_id' => Movie::inRandomOrder()->first()->id ?? Movie::factory(),
        ];
    }
}