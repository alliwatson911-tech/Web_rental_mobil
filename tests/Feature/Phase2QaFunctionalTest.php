<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Category;
use App\Models\Location;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase2QaFunctionalTest extends TestCase
{
    use RefreshDatabase;

    private Location $poolLocation;
    private Location $deliveryLocation;
    private Car $car;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::factory()->create(['name' => 'MPV']);
        $this->car = Car::factory()->create([
            'category_id' => $category->id,
            'daily_price' => 350000,
            'status' => 'available',
        ]);

        $this->poolLocation = Location::factory()->create([
            'name' => 'Kantor Padaidi Rental (Pool)',
            'requires_address' => false,
            'is_active' => true,
        ]);

        $this->deliveryLocation = Location::factory()->create([
            'name' => 'Antar ke Alamat Penyewa',
            'requires_address' => true,
            'is_active' => true,
        ]);
    }

    /**
     * UJI REPRODUKSI BUG F1-SEC-01:
     * Member terautentikasi harus dapat melihat detail pesanannya sendiri
     * meskipun session 'booking_access' kosong (misal login dari perangkat lain).
     */
    public function test_member_login_dapat_mengakses_detail_pesanannya_sendiri_tanpa_session_booking_access(): void
    {
        $member = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $booking = Booking::factory()->create([
            'member_id' => $member->id,
            'user_id' => $member->id,
            'car_id' => $this->car->id,
            'customer_phone' => $member->phone,
            'status' => BookingStatus::Pending,
        ]);

        // Simulasikan session bersih (tanpa 'booking_access')
        $response = $this->actingAs($member)->get(route('bookings.show', $booking));

        // Ekspektasi sistem produksi: Akses berhasil (200 OK) menampilkan kode pesanan
        $response->assertOk();
        $response->assertSee($booking->code);
    }

    /**
     * UJI KEAMANAN IDOR:
     * Member A tidak boleh dapat melihat pesanan milik Member B.
     */
    public function test_member_tidak_bisa_mengakses_pesanan_milik_member_lain_idor_prevention(): void
    {
        $memberA = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $memberB = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $bookingB = Booking::factory()->create([
            'member_id' => $memberB->id,
            'user_id' => $memberB->id,
            'car_id' => $this->car->id,
            'customer_phone' => $memberB->phone,
            'status' => BookingStatus::Pending,
        ]);

        // Member A mencoba mengakses pesanan Member B tanpa session booking_access
        $response = $this->actingAs($memberA)->get(route('bookings.show', $bookingB));

        // Harus ditolak (redirect ke cek-pesanan atau 403 Forbidden)
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    /**
     * UJI VALIDASI JALUR NEGATIF FORMULIR PEMESANAN:
     * Memastikan semua aturan batas input divalidasi dengan benar oleh server.
     */
    public function test_validasi_formulir_pemesanan_jalur_negatif(): void
    {
        // 1. Tanggal ambil di masa lalu
        $responsePastDate = $this->post(route('bookings.store', $this->car), [
            'pickup_location_id' => $this->poolLocation->id,
            'pickup_date' => now()->subDay()->toDateString(),
            'return_date' => now()->addDays(2)->toDateString(),
            'customer_name' => 'Fadli Tester',
            'customer_phone' => '081234567890',
            'agree_terms' => '1',
        ]);
        $responsePastDate->assertSessionHasErrors('pickup_date');

        // 2. Tanggal kembali mendahului tanggal ambil
        $responseInvalidReturn = $this->post(route('bookings.store', $this->car), [
            'pickup_location_id' => $this->poolLocation->id,
            'pickup_date' => now()->addDays(5)->toDateString(),
            'return_date' => now()->addDays(3)->toDateString(),
            'customer_name' => 'Fadli Tester',
            'customer_phone' => '081234567890',
            'agree_terms' => '1',
        ]);
        $responseInvalidReturn->assertSessionHasErrors('return_date');

        // 3. Durasi sewa 0 hari (pickup == return)
        $sameDay = now()->addDays(5)->toDateString();
        $responseZeroDays = $this->post(route('bookings.store', $this->car), [
            'pickup_location_id' => $this->poolLocation->id,
            'pickup_date' => $sameDay,
            'return_date' => $sameDay,
            'customer_name' => 'Fadli Tester',
            'customer_phone' => '081234567890',
            'agree_terms' => '1',
        ]);
        $responseZeroDays->assertSessionHasErrors('return_date');

        // 4. Nomor telepon format tidak valid (mengandung huruf)
        $responseInvalidPhone = $this->post(route('bookings.store', $this->car), [
            'pickup_location_id' => $this->poolLocation->id,
            'pickup_date' => now()->addDays(5)->toDateString(),
            'return_date' => now()->addDays(7)->toDateString(),
            'customer_name' => 'Fadli Tester',
            'customer_phone' => 'ABC-BUKAN-HP',
            'agree_terms' => '1',
        ]);
        $responseInvalidPhone->assertSessionHasErrors('customer_phone');

        // 5. Belum mencentang persetujuan syarat rental
        $responseUncheckedTerms = $this->post(route('bookings.store', $this->car), [
            'pickup_location_id' => $this->poolLocation->id,
            'pickup_date' => now()->addDays(5)->toDateString(),
            'return_date' => now()->addDays(7)->toDateString(),
            'customer_name' => 'Fadli Tester',
            'customer_phone' => '081234567890',
            // agree_terms tidak dikirim
        ]);
        $responseUncheckedTerms->assertSessionHasErrors('agree_terms');

        // 6. Alamat wajib jika lokasi membutuhkan antar ke alamat
        $responseMissingDeliveryAddress = $this->post(route('bookings.store', $this->car), [
            'pickup_location_id' => $this->deliveryLocation->id,
            'pickup_date' => now()->addDays(5)->toDateString(),
            'return_date' => now()->addDays(7)->toDateString(),
            'customer_name' => 'Fadli Tester',
            'customer_phone' => '081234567890',
            'agree_terms' => '1',
            // delivery_address kosong
        ]);
        $responseMissingDeliveryAddress->assertSessionHasErrors('delivery_address');
    }

    /**
     * UJI KASUS BATAS PROMO KEDALUWARSA / TIDAK VALID:
     * Kupon promo kedaluwarsa atau kuota habis tidak boleh diterapkan.
     */
    public function test_promo_kedaluwarsa_atau_tidak_valid_ditolak(): void
    {
        $expiredPromo = Promotion::create([
            'code' => 'EXPIRED50',
            'name' => 'Promo Diskon Kedaluwarsa',
            'target_type' => Promotion::TARGET_COUPON,
            'type' => Promotion::TYPE_NOMINAL,
            'value' => 50000,
            'min_order_amount' => 100000,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDay(), // Kemarin
            'is_active' => true,
        ]);

        $response = $this->post(route('bookings.store', $this->car), [
            'pickup_location_id' => $this->poolLocation->id,
            'pickup_date' => now()->addDays(3)->toDateString(),
            'return_date' => now()->addDays(5)->toDateString(),
            'customer_name' => 'Pelanggan Uji',
            'customer_phone' => '081234567890',
            'agree_terms' => '1',
            'promo_code' => 'EXPIRED50',
        ]);

        // Booking tetap bisa dibuat, namun promo_discount harus 0 (tidak mendapat potongan harga kedaluwarsa)
        $booking = Booking::latest('id')->first();
        $this->assertNotNull($booking);
        $this->assertEquals(0, $booking->promo_discount);
    }

    /**
     * UJI KEAMANAN UPLOAD DOKUMEN:
     * Server wajib menolak berkas non-gambar atau script berbahaya (.php/.exe).
     */
    public function test_unggah_dokumen_menolak_berkas_berbahaya(): void
    {
        Storage::fake('local');

        $booking = Booking::factory()->create(['status' => BookingStatus::Confirmed]);
        $session = ['booking_access' => [$booking->code]];

        // Upload berkas palsu .php
        $maliciousFile = UploadedFile::fake()->create('shell.php', 100, 'text/x-php');

        $response = $this->withSession($session)->post(route('bookings.document', $booking), [
            'type' => 'ktp',
            'document' => $maliciousFile,
        ]);

        $response->assertSessionHasErrors('document');
        $this->assertDatabaseMissing('customer_documents', [
            'booking_id' => $booking->id,
            'file_name' => 'shell.php',
        ]);
    }

    /**
     * UJI PENANGANAN KODE / SLUG TIDAK DITEMUKAN (404 HANDLING):
     */
    public function test_penanganan_rute_tidak_ditemukan_memberikan_404_bersih(): void
    {
        $this->get('/armada/mobil-fiktif-yang-tidak-ada-di-database')->assertNotFound();
        $this->get('/pesanan/PDD-NONEXISTENT')->assertNotFound();
    }

    /**
     * UJI HEADER KEAMANAN HTTP (OWASP RECOMMENDED):
     */
    public function test_header_keamanan_http_aktif_pada_respon(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * UJI ATURAN BISNIS KETERSEDIAAN [ambil, kembali):
     * Mobil yang kembali tanggal 12 boleh diambil pelanggan lain tanggal 12.
     */
    public function test_ketersediaan_rentang_sewa_setengah_terbuka(): void
    {
        $pickupA = now()->addDays(5)->toDateString();
        $returnA = now()->addDays(7)->toDateString(); // Kembali tanggal H+7

        // Pesanan A berstatus Confirmed
        Booking::factory()->create([
            'car_id' => $this->car->id,
            'pickup_date' => $pickupA,
            'return_date' => $returnA,
            'status' => BookingStatus::Confirmed,
        ]);

        // Pesanan B mulai di hari Pesanan A kembali (H+7 sampai H+9)
        $pickupB = $returnA;
        $returnB = now()->addDays(9)->toDateString();

        $response = $this->post(route('bookings.store', $this->car), [
            'pickup_location_id' => $this->poolLocation->id,
            'pickup_date' => $pickupB,
            'return_date' => $returnB,
            'customer_name' => 'Penyewa Hari Kembali',
            'customer_phone' => '081234567890',
            'agree_terms' => '1',
        ]);

        // Pesanan B harus berhasil dibuat karena rentang [p, r) tidak beririsan
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'Penyewa Hari Kembali',
            'pickup_date' => $pickupB,
        ]);
    }

    /**
     * UJI VERIFIKASI AKSES GUEST DENGAN NOMOR HP:
     */
    public function test_guest_dapat_mengakses_pesanan_setelah_verifikasi_nomor_hp(): void
    {
        $booking = Booking::factory()->create([
            'car_id' => $this->car->id,
            'customer_phone' => '081234567890',
            'status' => BookingStatus::Pending,
        ]);

        // Sebelum verifikasi, akses ditolak & dialihkan ke bookings.lookup
        $this->get(route('bookings.show', $booking))->assertRedirect(route('bookings.lookup', ['code' => $booking->code]));

        // Guest melakukan verifikasi melalui form cek-pesanan
        $responseVerify = $this->post(route('bookings.lookup.submit'), [
            'code' => $booking->code,
            'customer_phone' => '081234567890',
        ]);

        $responseVerify->assertRedirect(route('bookings.show', $booking));

        // Setelah verifikasi, sesi 'booking_access' menyimpan kode sehingga akses diizinkan (200 OK)
        $this->get(route('bookings.show', $booking))->assertOk()->assertSee($booking->code);
    }

    /**
     * UJI AKSES ADMIN KE DETAIL PESANAN:
     */
    public function test_admin_selalu_dapat_melihat_detail_pesanan_apapun(): void
    {
        $admin = User::factory()->admin()->create();
        $booking = Booking::factory()->create([
            'car_id' => $this->car->id,
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->get(route('bookings.show', $booking));
        $response->assertOk()->assertSee($booking->code);
    }
}
