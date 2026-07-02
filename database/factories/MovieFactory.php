<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'year' => $this->faker->numberBetween(1990, 2026),
            'poster_path' => 'posters/placeholder.jpg',
        ];
    }
}