<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['City Car', 'MPV', 'SUV', 'Premium', 'Minibus', 'Pickup']).' '.fake()->unique()->numberBetween(1, 9999);

        return ['name' => $name, 'slug' => Str::slug($name), 'description' => null, 'sort_order' => 0];
    }
}
