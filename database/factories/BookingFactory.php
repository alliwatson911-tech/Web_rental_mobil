<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Car;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        $pickup = now()->addDays(5)->startOfDay();
        $return = $pickup->copy()->addDays(2);

        return [
            'code' => 'PDD-'.strtoupper(fake()->unique()->bothify('??####')),
            'car_id' => Car::factory(),
            'pickup_location_id' => Location::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => '08'.fake()->numerify('##########'),
            'customer_email' => fake()->safeEmail(),
            'pickup_date' => $pickup->toDateString(),
            'return_date' => $return->toDateString(),
            'days' => 2,
            'daily_price' => 350000,
            'base_total' => 700000,
            'addons_total' => 0,
            'discount' => 0,
            'total' => 700000,
            'status' => BookingStatus::Pending,
        ];
    }

    public function status(BookingStatus $status): static
    {
        return $this->state(['status' => $status]);
    }

    /** Pesanan untuk mobil tertentu pada tanggal tertentu. */
    public function forDates(Car $car, string $pickup, string $return): static
    {
        return $this->state(fn () => ['car_id' => $car->id, 'pickup_date' => $pickup, 'return_date' => $return]);
    }
}
