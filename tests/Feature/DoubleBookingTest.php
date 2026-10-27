<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Exceptions\CarUnavailableException;
use App\Models\Booking;
use App\Models\Car;
use App\Models\CarBlock;
use App\Models\Location;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoubleBookingTest extends TestCase
{
    use RefreshDatabase;

    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->location = Location::factory()->create();
    }

    private function payload(string $pickup, string $return, string $name = 'Pelanggan'): array
    {
        return [
            'pickup_location_id' => $this->location->id,
            'pickup_date' => $pickup,
            'return_date' => $return,
            'customer_name' => $name,
            'customer_phone' => '081234567890',
            'agree_terms' => '1',
        ];
    }

    public function test_pesanan_kedua_untuk_tanggal_beririsan_ditolak(): void
    {
        $car = Car::factory()->create();

        $this->post(route('bookings.store', $car), $this->payload($this->day(3), $this->day(6), 'Pertama'))
            ->assertRedirect();
        $this->assertSame(1, Booking::count());

        $this->post(route('bookings.store', $car), $this->payload($this->day(5), $this->day(8), 'Kedua'))
            ->assertRedirect()
            ->assertSessionHasErrors('pickup_date');

        $this->assertSame(1, Booking::count());
        $this->assertSame('Pertama', Booking::first()->customer_name);
    }

    public function test_pesanan_dengan_tanggal_persis_sama_ditolak(): void
    {
        $car = Car::factory()->create();
        $this->post(route('bookings.store', $car), $this->payload($this->day(3), $this->day(4)));
        $this->post(route('bookings.store', $car), $this->payload($this->day(3), $this->day(4)))->assertSessionHasErrors('pickup_date');

        $this->assertSame(1, Booking::count());
    }

    public function test_pesanan_berurutan_tanpa_irisan_diterima(): void
    {
        $car = Car::factory()->create();

        $this->post(route('bookings.store', $car), $this->payload($this->day(3), $this->day(5)))->assertSessionHasNoErrors();
        // Diambil tepat pada tanggal mobil kembali
        $this->post(route('bookings.store', $car), $this->payload($this->day(5), $this->day(7)))->assertSessionHasNoErrors();

        $this->assertSame(2, Booking::count());
    }

    public function test_mobil_lain_tetap_bisa_dipesan_pada_tanggal_sama(): void
    {
        [$a, $b] = Car::factory()->count(2)->create();
        $this->post(route('bookings.store', $a), $this->payload($this->day(3), $this->day(5)))->assertSessionHasNoErrors();
        $this->post(route('bookings.store', $b), $this->payload($this->day(3), $this->day(5)))->assertSessionHasNoErrors();

        $this->assertSame(2, Booking::count());
    }

    public function test_tanggal_bisa_dipesan_lagi_setelah_pesanan_dibatalkan(): void
    {
        $car = Car::factory()->create();
        Booking::factory()->forDates($car, $this->day(3), $this->day(5))->status(BookingStatus::Cancelled)->create();

        $this->post(route('bookings.store', $car), $this->payload($this->day(3), $this->day(5)))->assertSessionHasNoErrors();
        $this->assertSame(1, Booking::where('status', BookingStatus::Pending->value)->count());
    }

    public function test_service_menolak_pesanan_pada_jadwal_servis(): void
    {
        $car = Car::factory()->create();
        CarBlock::factory()->create(['car_id' => $car->id, 'start_date' => $this->day(4), 'end_date' => $this->day(4)]);

        $this->expectException(CarUnavailableException::class);

        app(BookingService::class)->create($car, $this->payload($this->day(3), $this->day(5)));
    }

    public function test_pemeriksaan_ulang_di_dalam_transaksi_mencegah_pesanan_ganda(): void
    {
        // Simulasi "balapan": dua pelanggan sama-sama melihat mobil tersedia,
        // lalu mengirim pesanan hampir bersamaan. Pesanan kedua harus gagal
        // karena BookingService memeriksa ulang ketersediaan di dalam transaksi
        // setelah mengunci baris mobil (lockForUpdate).
        $car = Car::factory()->create();
        $service = app(BookingService::class);

        $service->create($car, $this->payload($this->day(10), $this->day(12), 'A'));

        try {
            $service->create($car, $this->payload($this->day(11), $this->day(13), 'B'));
            $this->fail('Pesanan ganda seharusnya ditolak.');
        } catch (CarUnavailableException $e) {
            $this->assertStringContainsString('tidak tersedia', $e->getMessage());
        }

        $this->assertSame(1, Booking::where('car_id', $car->id)->count());
    }

    public function test_mobil_yang_tidak_available_tidak_bisa_dipesan(): void
    {
        $car = Car::factory()->maintenance()->create();

        $this->post(route('bookings.store', $car), $this->payload($this->day(3), $this->day(5)))->assertNotFound();
        $this->assertSame(0, Booking::count());
    }
}
