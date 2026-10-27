<?php

namespace Database\Factories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarBlockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(11)->toDateString(),
            'reason' => 'Servis berkala',
            'notes' => null,
        ];
    }
}
