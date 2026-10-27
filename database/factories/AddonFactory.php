<?php

namespace Database\Factories;

use App\Models\Addon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'addon-'.fake()->unique()->numberBetween(1, 99999),
            'name' => 'Tambahan '.fake()->word(),
            'description' => null,
            'price' => 50000,
            'charge_type' => Addon::PER_DAY,
            'is_active' => true,
        ];
    }

    public function once(): static
    {
        return $this->state(['charge_type' => Addon::ONCE]);
    }
}
