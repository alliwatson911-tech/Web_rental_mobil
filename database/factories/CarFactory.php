<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CarFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Toyota Avanza', 'Daihatsu Xenia', 'Honda Brio', 'Toyota Innova']);
        $plate = 'DD '.fake()->unique()->numberBetween(1000, 9999).' '.strtoupper(fake()->lexify('??'));

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name.' '.$plate),
            'brand' => strtok($name, ' '),
            'year' => fake()->numberBetween(2018, 2025),
            'plate_number' => $plate,
            'transmission' => 'manual',
            'seats' => 7,
            'fuel' => 'Bensin',
            'daily_price' => 350000,
            'status' => Car::STATUS_AVAILABLE,
            'description' => null,
        ];
    }

    public function maintenance(): static
    {
        return $this->state(['status' => Car::STATUS_MAINTENANCE]);
    }
}
