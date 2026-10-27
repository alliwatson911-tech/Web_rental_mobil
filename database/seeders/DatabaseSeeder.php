<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Addon;
use App\Models\Car;
use App\Models\CarBlock;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(BookingService $bookings): void
    {
        // Akun admin contoh - GANTI kata sandi setelah instalasi.
        User::updateOrCreate(
            ['email' => env('ADMIN_SEED_EMAIL', 'admin@padaidi.test')],
            [
                'name' => 'Admin Padaidi',
                'phone' => '6282197233174',
                'role' => User::ROLE_ADMIN,
                'password' => env('ADMIN_SEED_PASSWORD', 'padaidi-admin-123'),
                'email_verified_at' => now(),
            ]
        );

        $categories = collect([
            ['City Car', 'Mobil kecil irit untuk dalam kota.'],
            ['MPV', 'Mobil keluarga 7 penumpang.'],
            ['SUV', 'Tangguh untuk perjalanan luar kota.'],
            ['Premium', 'Mobil mewah untuk acara khusus.'],
        ])->mapWithKeys(fn ($c, $i) => [$c[0] => Category::create([
            'name' => $c[0], 'slug' => Str::slug($c[0]), 'description' => $c[1], 'sort_order' => $i + 1,
        ])]);

        $office = Location::create(['name' => 'Kantor Padaidi', 'slug' => 'kantor-padaidi', 'description' => config('padaidi.address'), 'requires_address' => false]);
        Location::create(['name' => 'Bandara Sultan Hasanuddin', 'slug' => 'bandara', 'description' => 'Penjemputan di area kedatangan bandara.', 'requires_address' => false]);
        Location::create(['name' => 'Antar ke alamat', 'slug' => 'antar-ke-alamat', 'description' => 'Mobil diantar ke alamat Anda (biaya antar-jemput berlaku).', 'requires_address' => true]);

        $driver = Addon::create(['code' => 'driver', 'name' => 'Sopir', 'description' => 'Sopir berpengalaman, maks. 12 jam per hari.', 'price' => 200000, 'charge_type' => Addon::PER_DAY]);
        $insurance = Addon::create(['code' => 'insurance', 'name' => 'Asuransi', 'description' => 'Perlindungan tambahan atas kerusakan ringan.', 'price' => 50000, 'charge_type' => Addon::PER_DAY]);
        Addon::create(['code' => Addon::CODE_DELIVERY, 'name' => 'Antar-jemput', 'description' => 'Mobil diantar & dijemput di alamat Anda (wilayah Makassar).', 'price' => 100000, 'charge_type' => Addon::ONCE]);

        $cars = [
            ['City Car', 'Toyota Agya', 'Toyota', 2022, 'DD 1201 PA', 'manual', 5, 300000],
            ['City Car', 'Honda Brio', 'Honda', 2023, 'DD 1202 PA', 'automatic', 5, 325000],
            ['MPV', 'Toyota Avanza', 'Toyota', 2022, 'DD 1203 PA', 'manual', 7, 375000],
            ['MPV', 'Mitsubishi Xpander', 'Mitsubishi', 2023, 'DD 1204 PA', 'automatic', 7, 425000],
            ['MPV', 'Toyota Innova Zenix', 'Toyota', 2024, 'DD 1205 PA', 'automatic', 7, 650000],
            ['SUV', 'Toyota Fortuner', 'Toyota', 2022, 'DD 1206 PA', 'automatic', 7, 1200000],
            ['SUV', 'Mitsubishi Pajero Sport', 'Mitsubishi', 2023, 'DD 1207 PA', 'automatic', 7, 1250000],
            ['Premium', 'Toyota Alphard', 'Toyota', 2022, 'DD 1208 PA', 'automatic', 7, 2500000],
        ];

        $created = [];
        foreach ($cars as [$cat, $name, $brand, $year, $plate, $trans, $seats, $price]) {
            $created[$name] = Car::create([
                'category_id' => $categories[$cat]->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'brand' => $brand,
                'year' => $year,
                'plate_number' => $plate,
                'transmission' => $trans,
                'seats' => $seats,
                'fuel' => 'Bensin',
                'daily_price' => $price,
                'status' => Car::STATUS_AVAILABLE,
                'description' => "{$name} {$year}, transmisi ".($trans === 'manual' ? 'manual' : 'otomatis').', AC dingin, bersih dan terawat.',
            ]);
        }

        // Contoh jadwal servis
        CarBlock::create([
            'car_id' => $created['Toyota Fortuner']->id,
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
            'reason' => 'Servis berkala',
        ]);

        // Contoh pesanan (harga dihitung oleh BookingService, sama seperti pesanan asli)
        $sample = fn (Car $car, int $startIn, int $days, array $addons, string $name, string $phone) => $bookings->create($car, [
            'pickup_location_id' => $office->id,
            'pickup_date' => now()->addDays($startIn)->toDateString(),
            'return_date' => now()->addDays($startIn + $days)->toDateString(),
            'addons' => $addons,
            'customer_name' => $name,
            'customer_phone' => $phone,
            'customer_email' => null,
        ]);

        $sample($created['Toyota Avanza'], 2, 3, [$insurance->id], 'Contoh Pelanggan Satu', '081200000001');
        $confirmed = $sample($created['Toyota Innova Zenix'], 4, 2, [$driver->id], 'Contoh Pelanggan Dua', '081200000002');
        $bookings->transition($confirmed, BookingStatus::Confirmed);
    }
}
