<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\CarBlock;
use App\Models\Category;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilitySearchTest extends TestCase
{
    use RefreshDatabase;

    private function search(string $pickup, string $return, array $extra = [])
    {
        return $this->get(route('cars.index', ['pickup_date' => $pickup, 'return_date' => $return] + $extra));
    }

    public function test_mobil_tersedia_muncul_di_hasil_pencarian(): void
    {
        $car = Car::factory()->create(['name' => 'Avanza Bebas']);

        $this->search($this->day(3), $this->day(5))
            ->assertOk()
            ->assertSee('Avanza Bebas');
    }

    public function test_mobil_dengan_pesanan_aktif_yang_beririsan_tidak_muncul(): void
    {
        foreach ([BookingStatus::Pending, BookingStatus::Confirmed, BookingStatus::Active] as $i => $status) {
            $car = Car::factory()->create(['name' => "Mobil Terpakai {$i}"]);
            Booking::factory()->forDates($car, $this->day(4), $this->day(6))->status($status)->create();
        }

        $response = $this->search($this->day(5), $this->day(7))->assertOk();
        $response->assertDontSee('Mobil Terpakai 0')->assertDontSee('Mobil Terpakai 1')->assertDontSee('Mobil Terpakai 2');
    }

    public function test_pesanan_selesai_atau_batal_tidak_menghalangi(): void
    {
        $a = Car::factory()->create(['name' => 'Mobil Batal']);
        $b = Car::factory()->create(['name' => 'Mobil Selesai']);
        Booking::factory()->forDates($a, $this->day(4), $this->day(6))->status(BookingStatus::Cancelled)->create();
        Booking::factory()->forDates($b, $this->day(4), $this->day(6))->status(BookingStatus::Completed)->create();

        $this->search($this->day(4), $this->day(6))->assertSee('Mobil Batal')->assertSee('Mobil Selesai');
    }

    public function test_mobil_yang_kembali_tanggal_x_boleh_diambil_tanggal_x(): void
    {
        $car = Car::factory()->create(['name' => 'Mobil Estafet']);
        Booking::factory()->forDates($car, $this->day(2), $this->day(5))->status(BookingStatus::Confirmed)->create();

        // Ambil tepat di tanggal kembali pesanan sebelumnya -> tersedia
        $this->search($this->day(5), $this->day(7))->assertSee('Mobil Estafet');
        // Kembali tepat di tanggal ambil pesanan berikutnya -> tersedia
        $this->search($this->day(1), $this->day(2))->assertSee('Mobil Estafet');
        // Beririsan satu hari -> tidak tersedia
        $this->search($this->day(4), $this->day(6))->assertDontSee('Mobil Estafet');
    }

    public function test_jadwal_servis_yang_beririsan_menyembunyikan_mobil(): void
    {
        $car = Car::factory()->create(['name' => 'Mobil Servis']);
        CarBlock::factory()->create(['car_id' => $car->id, 'start_date' => $this->day(6), 'end_date' => $this->day(7)]);

        $this->search($this->day(5), $this->day(7))->assertDontSee('Mobil Servis');
        // Tanggal selesai servis inklusif: hari ke-7 masih servis
        $this->search($this->day(7), $this->day(8))->assertDontSee('Mobil Servis');
        // Sewa berakhir (kembali) tepat di hari servis mulai -> boleh
        $this->search($this->day(4), $this->day(6))->assertSee('Mobil Servis');
        $this->search($this->day(8), $this->day(9))->assertSee('Mobil Servis');
    }

    public function test_mobil_berstatus_selain_available_tidak_muncul(): void
    {
        Car::factory()->maintenance()->create(['name' => 'Mobil Bengkel']);
        Car::factory()->create(['name' => 'Mobil Nonaktif', 'status' => Car::STATUS_INACTIVE]);

        $this->search($this->day(2), $this->day(3))->assertDontSee('Mobil Bengkel')->assertDontSee('Mobil Nonaktif');
    }

    public function test_filter_kategori(): void
    {
        $suv = Category::factory()->create(['name' => 'SUV', 'slug' => 'suv']);
        $city = Category::factory()->create(['name' => 'City Car', 'slug' => 'city-car']);
        Car::factory()->create(['name' => 'Fortuner Uji', 'category_id' => $suv->id]);
        Car::factory()->create(['name' => 'Agya Uji', 'category_id' => $city->id]);

        $this->search($this->day(2), $this->day(3), ['category' => 'suv'])
            ->assertSee('Fortuner Uji')->assertDontSee('Agya Uji');

        $this->get(route('cars.index', ['category' => 'city-car']))
            ->assertSee('Agya Uji')->assertDontSee('Fortuner Uji');
    }

    public function test_validasi_tanggal_kembali_harus_setelah_tanggal_ambil(): void
    {
        $this->search($this->day(5), $this->day(3))->assertSessionHasErrors('return_date');
        $this->search($this->day(-1), $this->day(3))->assertSessionHasErrors('pickup_date');
    }

    public function test_service_ketersediaan_mengabaikan_pesanan_tertentu(): void
    {
        $car = Car::factory()->create();
        $booking = Booking::factory()->forDates($car, $this->day(2), $this->day(4))->create();
        $service = app(AvailabilityService::class);

        $this->assertFalse($service->isAvailable($car, $this->day(3), $this->day(5)));
        $this->assertTrue($service->isAvailable($car, $this->day(3), $this->day(5), $booking->id));
    }
}
