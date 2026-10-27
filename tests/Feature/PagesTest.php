<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\CarBlock;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Uji asap: semua halaman utama tampil tanpa error dengan data seeder. */
class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_halaman_publik_tampil(): void
    {
        $car = Car::firstOrFail();

        $this->get('/')->assertOk()->assertSee('Cari mobil yang tersedia');
        $this->get(route('cars.index'))->assertOk()->assertSee('Toyota Avanza');
        $this->get(route('cars.show', $car))->assertOk()->assertSee($car->name);
        $this->get(route('cars.show', ['car' => $car, 'pickup_date' => $this->day(20), 'return_date' => $this->day(22)]))->assertOk()->assertSee('Tersedia untuk');
        $this->get(route('terms'))->assertOk()->assertSee('Deposit')->assertSee('Batas kilometer');
        $this->get(route('contact'))->assertOk()->assertSee('wa.me/6282197233174', false)->assertSee('wa.me/6282235585445', false);
        $this->get(route('bookings.create', ['car' => $car, 'pickup_date' => $this->day(20), 'return_date' => $this->day(22)]))->assertOk()->assertSee('Lihat ringkasan');
        $this->get(route('bookings.lookup'))->assertOk();
    }

    public function test_alur_pelanggan_pesan_cek_status_dan_unggah_bukti(): void
    {
        Storage::fake('public');
        $car = Car::where('name', 'Toyota Agya')->firstOrFail();
        $location = Location::where('slug', 'kantor-padaidi')->firstOrFail();

        $response = $this->post(route('bookings.store', $car), [
            'pickup_location_id' => $location->id,
            'pickup_date' => $this->day(20),
            'return_date' => $this->day(22),
            'customer_name' => 'Andi Uji',
            'customer_phone' => '0812 3456 7890',
            'agree_terms' => '1',
        ]);
        $booking = Booking::where('customer_name', 'Andi Uji')->firstOrFail();
        $response->assertRedirect(route('bookings.show', $booking));

        $this->get(route('bookings.show', $booking))->assertOk()
            ->assertSee($booking->code)->assertSee('Rp 600.000')->assertSee('wa.me/6282197233174', false);

        $this->post(route('bookings.payment', $booking), [
            'amount' => 600000, 'sender_name' => 'Andi Uji',
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, $booking->payments()->count());

        // Pengunjung lain (sesi baru) harus memasukkan nomor HP yang benar
        $this->flushSession();
        $this->get(route('bookings.show', $booking))->assertRedirect(route('bookings.lookup', ['code' => $booking->code]));
        $this->post(route('bookings.lookup.submit'), ['code' => $booking->code, 'customer_phone' => '0899999999'])->assertSessionHasErrors('code');
        $this->post(route('bookings.lookup.submit'), ['code' => strtolower($booking->code), 'customer_phone' => '+62 812-3456-7890'])
            ->assertRedirect(route('bookings.show', $booking));
    }

    public function test_halaman_admin_tampil(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $booking = Booking::where('status', BookingStatus::Confirmed->value)->firstOrFail();
        $car = Car::firstOrFail();

        $this->actingAs($admin);
        foreach ([
            route('admin.dashboard'), route('admin.bookings.index'), route('admin.bookings.index', ['status' => 'pending']),
            route('admin.bookings.show', $booking), route('admin.payments.index'), route('admin.cars.index'),
            route('admin.cars.create'), route('admin.cars.edit', $car), route('admin.addons.index'), route('admin.addons.create'),
            route('admin.locations.index'), route('admin.locations.create'), route('admin.blocks.index'), route('admin.blocks.create'),
            route('admin.blocks.edit', CarBlock::firstOrFail()),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_login_admin(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.login.submit'), ['email' => 'admin@padaidi.test', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->post(route('admin.login.submit'), ['email' => 'admin@padaidi.test', 'password' => 'padaidi-admin-123'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_admin_crud_mobil_dengan_foto(): void
    {
        Storage::fake('public');
        $admin = User::where('role', 'admin')->firstOrFail();
        $category = Category::firstOrFail();

        $this->actingAs($admin)->post(route('admin.cars.store'), [
            'category_id' => $category->id, 'name' => 'Suzuki Ertiga', 'brand' => 'Suzuki', 'year' => 2023,
            'plate_number' => 'DD 9999 ZZ', 'transmission' => 'manual', 'seats' => 7, 'fuel' => 'Bensin',
            'daily_price' => 350000, 'status' => 'available',
            'photos' => [UploadedFile::fake()->image('depan.jpg'), UploadedFile::fake()->image('samping.jpg')],
        ])->assertSessionHasNoErrors();

        $car = Car::where('plate_number', 'DD 9999 ZZ')->firstOrFail();
        $this->assertSame(2, $car->photos()->count());
        $this->assertSame(1, $car->photos()->where('is_primary', true)->count());

        $this->actingAs($admin)->put(route('admin.cars.update', $car), [
            'category_id' => $category->id, 'name' => 'Suzuki Ertiga Hybrid', 'brand' => 'Suzuki', 'year' => 2024,
            'plate_number' => 'DD 9999 ZZ', 'transmission' => 'automatic', 'seats' => 7, 'fuel' => 'Bensin',
            'daily_price' => 375000, 'status' => 'available',
        ])->assertSessionHasNoErrors();
        $this->assertSame(375000, $car->refresh()->daily_price);

        $this->actingAs($admin)->delete(route('admin.cars.destroy', $car))->assertSessionHasNoErrors();
        $this->assertModelMissing($car);
    }
}
