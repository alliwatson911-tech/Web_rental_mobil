<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LocationFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Lokasi '.fake()->unique()->numberBetween(1, 99999);

        return ['name' => $name, 'slug' => Str::slug($name), 'description' => null, 'requires_address' => false, 'is_active' => true];
    }

    public function delivery(): static
    {
        return $this->state(['requires_address' => true]);
    }
}
