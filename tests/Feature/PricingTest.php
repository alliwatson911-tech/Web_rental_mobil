<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Location;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingTest extends TestCase
{
    use RefreshDatabase;

    private function bookingPayload(Location $location, array $overrides = []): array
    {
        return array_merge([
            'pickup_location_id' => $location->id,
            'pickup_date' => $this->day(3),
            'return_date' => $this->day(6),
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'customer_email' => 'budi@example.test',
            'agree_terms' => '1',
        ], $overrides);
    }

    public function test_jumlah_hari_adalah_selisih_tanggal_minimal_satu(): void
    {
        $pricing = new PricingService;

        $this->assertSame(3, $pricing->days('2025-08-10', '2025-08-13'));
        $this->assertSame(1, $pricing->days('2025-08-10', '2025-08-11'));
        $this->assertSame(1, $pricing->days('2025-08-10', '2025-08-10'));
        // Melewati pergantian bulan
        $this->assertSame(3, $pricing->days('2025-08-30', '2025-09-02'));
    }

    public function test_total_harga_dengan_tambahan_per_hari_dan_sekali_bayar(): void
    {
        $car = Car::factory()->create(['daily_price' => 300000]);
        $driver = Addon::factory()->create(['price' => 150000, 'charge_type' => Addon::PER_DAY]);
        $insurance = Addon::factory()->create(['price' => 50000, 'charge_type' => Addon::PER_DAY]);
        $delivery = Addon::factory()->once()->create(['price' => 100000]);

        $quote = (new PricingService)->quote($car, '2025-08-10', '2025-08-13', [$driver, $insurance, $delivery]);

        $this->assertSame(3, $quote['days']);
        $this->assertSame(900000, $quote['base_total']);     // 300.000 x 3
        $this->assertSame(700000, $quote['addons_total']);   // 150.000x3 + 50.000x3 + 100.000
        $this->assertSame(1600000, $quote['total']);
    }

    public function test_diskon_mengurangi_total_dan_tidak_bisa_minus(): void
    {
        $car = Car::factory()->create(['daily_price' => 300000]);
        $pricing = new PricingService;

        $this->assertSame(500000, $pricing->quote($car, '2025-08-10', '2025-08-12', [], 100000)['total']);
        $this->assertSame(0, $pricing->quote($car, '2025-08-10', '2025-08-12', [], 9999999)['total']);
    }

    public function test_total_dari_browser_diabaikan_dan_dihitung_ulang_di_server(): void
    {
        $car = Car::factory()->create(['daily_price' => 400000]);
        $location = Location::factory()->create();
        $insurance = Addon::factory()->create(['price' => 50000, 'charge_type' => Addon::PER_DAY]);

        $this->post(route('bookings.store', $car), $this->bookingPayload($location, [
            'addons' => [$insurance->id],
            'total' => 1000,          // upaya manipulasi harga dari browser
            'daily_price' => 1,
            'discount' => 999999,
        ]))->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertSame(3, $booking->days);
        $this->assertSame(400000, $booking->daily_price);
        $this->assertSame(1200000, $booking->base_total);
        $this->assertSame(150000, $booking->addons_total);
        $this->assertSame(0, $booking->discount);
        $this->assertSame(1350000, $booking->total);
    }

    public function test_harga_disalin_ke_pesanan_dan_tidak_berubah_saat_harga_naik(): void
    {
        $car = Car::factory()->create(['daily_price' => 300000]);
        $location = Location::factory()->create();
        $driver = Addon::factory()->create(['name' => 'Sopir', 'price' => 200000, 'charge_type' => Addon::PER_DAY]);

        $this->post(route('bookings.store', $car), $this->bookingPayload($location, ['addons' => [$driver->id]]));
        $booking = Booking::with('addons')->firstOrFail();
        $this->assertSame(1500000, $booking->total); // 300.000x3 + 200.000x3

        // Harga dinaikkan setelah pesanan dibuat
        $car->update(['daily_price' => 999000]);
        $driver->update(['price' => 999000]);

        $booking->refresh()->load('addons');
        $this->assertSame(300000, $booking->daily_price);
        $this->assertSame(1500000, $booking->total);
        $this->assertSame(200000, $booking->addons->first()->unit_price);
        $this->assertSame('Sopir', $booking->addons->first()->name);
    }

    public function test_lokasi_antar_ke_alamat_wajib_alamat_dan_otomatis_menambah_antar_jemput(): void
    {
        $car = Car::factory()->create(['daily_price' => 300000]);
        $deliveryLocation = Location::factory()->delivery()->create();
        Addon::factory()->once()->create(['code' => Addon::CODE_DELIVERY, 'name' => 'Antar-jemput', 'price' => 100000]);

        $this->post(route('bookings.store', $car), $this->bookingPayload($deliveryLocation))
            ->assertSessionHasErrors('delivery_address');

        $this->post(route('bookings.store', $car), $this->bookingPayload($deliveryLocation, ['delivery_address' => 'Jl. Contoh No. 5']))
            ->assertRedirect();

        $booking = Booking::with('addons')->firstOrFail();
        $this->assertSame(1000000, $booking->total); // 300.000x3 + 100.000
        $this->assertSame('Antar-jemput', $booking->addons->first()->name);
    }

    public function test_halaman_ringkasan_menampilkan_total_dari_server(): void
    {
        $car = Car::factory()->create(['daily_price' => 250000]);
        $location = Location::factory()->create();

        $this->post(route('bookings.review', $car), $this->bookingPayload($location, ['total' => 1]))
            ->assertOk()
            ->assertSee('Rp 750.000');

        $this->assertSame(0, Booking::count(), 'Ringkasan tidak boleh menyimpan pesanan');
    }
}
