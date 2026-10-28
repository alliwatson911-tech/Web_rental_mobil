<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Category;
use App\Models\CustomerDocument;
use App\Models\Location;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EnhancementsAndKycTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_2_dapat_login_ke_panel_admin(): void
    {
        $admin2 = User::create([
            'name' => 'Admin Padaidi 2 (Operasional)',
            'email' => 'admin2@padaidi.test',
            'phone' => '6282235585445',
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make('admin123'),
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => 'admin2@padaidi.test',
            'password' => 'admin123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin2);
    }

    public function test_admin_login_meskipun_password_di_database_plain_text(): void
    {
        // Simulasi jika password di DB tidak sengaja di-update secara plain-text
        $admin = User::forceCreate([
            'name' => 'Admin Padaidi',
            'email' => 'admin@padaidi.test',
            'phone' => '6282197233174',
            'role' => User::ROLE_ADMIN,
            'password' => 'admin123', // plain text unhashed
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => 'admin@padaidi.test',
            'password' => 'admin123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);

        // Password otomatis diperbarui menjadi hash bcrypt yang aman
        $admin->refresh();
        $this->assertTrue(str_starts_with($admin->password, '$2y$'));
    }

    public function test_rute_login_publik_menampilkan_halaman_login_member(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_pelanggan_dapat_mengunggah_dokumen_ekyc_ktp_dan_sim(): void
    {
        Storage::fake('local');

        $booking = Booking::factory()->create(['status' => BookingStatus::Confirmed]);

        // Akses session diberikan untuk kode booking ini
        $session = ['booking_access' => [$booking->code]];

        $fileKtp = UploadedFile::fake()->image('ktp.jpg');
        $this->withSession($session)
            ->post(route('bookings.document', $booking), [
                'type' => 'ktp',
                'document' => $fileKtp,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('customer_documents', [
            'booking_id' => $booking->id,
            'type' => 'ktp',
            'status' => CustomerDocument::STATUS_PENDING,
        ]);

        $fileSim = UploadedFile::fake()->image('sim.jpg');
        $this->withSession($session)
            ->post(route('bookings.document', $booking), [
                'type' => 'sim',
                'document' => $fileSim,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('customer_documents', [
            'booking_id' => $booking->id,
            'type' => 'sim',
            'status' => CustomerDocument::STATUS_PENDING,
        ]);

        $booking->refresh();
        $this->assertTrue($booking->isKycComplete());
    }

    public function test_admin_dapat_memverifikasi_dokumen_ekyc_pelanggan(): void
    {
        $admin = User::factory()->admin()->create();
        $booking = Booking::factory()->create();
        $doc = CustomerDocument::create([
            'booking_id' => $booking->id,
            'type' => CustomerDocument::TYPE_KTP,
            'path' => 'customer_documents/ktp.jpg',
            'status' => CustomerDocument::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.documents.verify', $doc), [
                'notes' => 'Dokumen asli terverifikasi',
            ])
            ->assertRedirect();

        $doc->refresh();
        $this->assertSame(CustomerDocument::STATUS_VERIFIED, $doc->status);
        $this->assertSame($admin->id, $doc->verified_by);
        $this->assertNotNull($doc->verified_at);
    }

    public function test_akses_dokumen_dan_bukti_bayar_terproteksi_dari_akses_publik(): void
    {
        Storage::fake('local');
        $booking = Booking::factory()->create();

        $pathDoc = UploadedFile::fake()->image('ktp.jpg')->store('customer_documents', 'local');
        $doc = CustomerDocument::create([
            'booking_id' => $booking->id,
            'type' => 'ktp',
            'path' => $pathDoc,
        ]);

        $pathProof = UploadedFile::fake()->image('proof.jpg')->store('payments', 'local');
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 500000,
            'method' => 'transfer',
            'proof_path' => $pathProof,
            'status' => Payment::STATUS_PENDING,
        ]);

        // 1. Publik tanpa session / tanpa login admin harus ditolak (403)
        $this->get(route('bookings.document.show', [$booking, $doc]))
            ->assertForbidden();

        $this->get(route('bookings.payment.proof', [$booking, $payment]))
            ->assertForbidden();

        // 2. Pelanggan dengan hak akses session dapat melihat berkas miliknya
        $this->withSession(['booking_access' => [$booking->code]])
            ->get(route('bookings.document.show', [$booking, $doc]))
            ->assertOk();

        // 3. Admin dapat melihat berkas
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get(route('bookings.document.show', [$booking, $doc]))
            ->assertOk();
    }

    public function test_admin_dapat_mencatat_inspeksi_serah_terima_dan_pengembalian_unit(): void
    {
        $admin = User::factory()->admin()->create();
        $booking = Booking::factory()->create(['status' => BookingStatus::Confirmed]);

        // Serah terima unit (Pickup / Handover)
        $this->actingAs($admin)
            ->post(route('admin.bookings.pickup', $booking), [
                'start_odometer' => 45000,
                'fuel_out' => 'Full (Penuh)',
                'handover_notes' => 'Baret halus pintu kanan',
                'driver_name' => 'Pak Budi',
                'driver_phone' => '08123456789',
            ])
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame(BookingStatus::Active, $booking->status);
        $this->assertSame(45000, $booking->start_odometer);
        $this->assertSame('Full (Penuh)', $booking->fuel_out);
        $this->assertSame('Pak Budi', $booking->driver_name);

        // Pengembalian unit (Return / Complete)
        $this->actingAs($admin)
            ->post(route('admin.bookings.complete', $booking), [
                'end_odometer' => 45350,
                'fuel_in' => 'Full (Penuh)',
                'return_notes' => 'Kondisi mulus tanpa baret baru',
                'overtime_hours' => 2,
                'overtime_fee' => 100000,
                'extra_fee' => 0,
            ])
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame(BookingStatus::Completed, $booking->status);
        $this->assertSame(45350, $booking->end_odometer);
        $this->assertSame(350, $booking->distanceDriven());
        $this->assertSame(100000, $booking->overtime_fee);
        $this->assertSame($booking->total + 100000, $booking->grandTotal());
    }

    public function test_filter_katalog_berdasarkan_transmisi_dan_kapasitas(): void
    {
        $cat = Category::factory()->create();
        Car::factory()->create([
            'category_id' => $cat->id,
            'name' => 'Agya Manual 5-seat',
            'transmission' => 'manual',
            'seats' => 5,
            'status' => Car::STATUS_AVAILABLE,
        ]);
        Car::factory()->create([
            'category_id' => $cat->id,
            'name' => 'Innova Matic 7-seat',
            'transmission' => 'automatic',
            'seats' => 7,
            'status' => Car::STATUS_AVAILABLE,
        ]);

        // Filter automatic
        $this->get(route('cars.index', ['transmission' => 'automatic']))
            ->assertOk()
            ->assertSee('Innova Matic 7-seat')
            ->assertDontSee('Agya Manual 5-seat');

        // Filter manual
        $this->get(route('cars.index', ['transmission' => 'manual']))
            ->assertOk()
            ->assertSee('Agya Manual 5-seat')
            ->assertDontSee('Innova Matic 7-seat');

        // Filter seats >= 7
        $this->get(route('cars.index', ['seats' => 7]))
            ->assertOk()
            ->assertSee('Innova Matic 7-seat')
            ->assertDontSee('Agya Manual 5-seat');
    }
}

