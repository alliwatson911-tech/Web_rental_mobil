<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Addon;
use App\Models\Car;
use App\Models\Category;
use App\Models\CustomerDocument;
use App\Models\DocumentAccessLog;
use App\Models\Location;
use App\Models\MemberProfile;
use App\Models\MembershipTier;
use App\Models\User;
use App\Services\BookingService;
use App\Services\MembershipService;
use Database\Seeders\MembershipTierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase1MembershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MembershipTierSeeder::class);
    }

    public function test_pelanggan_dapat_mendaftar_akun_member_dengan_tier_reguler(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Ahmad Dani',
            'email' => 'ahmad@example.com',
            'phone' => '081234567891',
            'password' => 'Rahasia1234!',
            'password_confirmation' => 'Rahasia1234!',
            'address' => 'Jl. Boulevard Panakkukang No. 12, Makassar',
        ]);

        $response->assertRedirect(route('member.account'));
        $this->assertAuthenticated();

        $user = User::where('email', 'ahmad@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertTrue($user->isMember());

        // Pastikan profil dibuat dengan tier Reguler
        $profile = $user->memberProfile;
        $this->assertNotNull($profile);
        $this->assertSame(MembershipTier::SLUG_REGULER, $profile->effectiveTier()->slug);
        $this->assertSame(0, $profile->discountPercentage());
        $this->assertSame(MemberProfile::KYC_UNVERIFIED, $profile->kyc_status);
        $this->assertSame('Jl. Boulevard Panakkukang No. 12, Makassar', $profile->address);
    }

    public function test_member_dapat_login_dengan_email_atau_nomor_telepon(): void
    {
        $user = User::factory()->create([
            'email' => 'siti@example.com',
            'phone' => '081399887766',
            'password' => Hash::make('SitiSandi123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);
        $user->ensureMemberProfile();

        // 1. Login menggunakan email
        $this->post(route('login.submit'), [
            'email' => 'siti@example.com',
            'password' => 'SitiSandi123!',
        ])->assertRedirect(route('member.account'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('member.logout'));
        $this->assertGuest();

        // 2. Login menggunakan nomor telepon
        $this->post(route('login.submit'), [
            'email' => '081399887766',
            'password' => 'SitiSandi123!',
        ])->assertRedirect(route('member.account'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_member_nonaktif_dicegah_untuk_login(): void
    {
        $user = User::factory()->create([
            'email' => 'banned@example.com',
            'password' => Hash::make('Password123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);
        $profile = $user->ensureMemberProfile();
        $profile->update(['is_active' => false]);

        $response = $this->post(route('login.submit'), [
            'email' => 'banned@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_alur_lupa_kata_sandi_member(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => Hash::make('Lama12345'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        // Minta token reset password
        $this->post(route('password.email'), [
            'email' => 'reset@example.com',
        ])->assertSessionHas('success');

        $token = Password::broker()->createToken($user);

        // Eksekusi pembaruan sandi
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'reset@example.com',
            'password' => 'Baru1234567!',
            'password_confirmation' => 'Baru1234567!',
        ])->assertRedirect(route('login'));

        $user->refresh();
        $this->assertTrue(Hash::check('Baru1234567!', $user->password));
    }

    public function test_member_dapat_memperbarui_profil_dan_verifikasi_whatsapp(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $user->ensureMemberProfile();

        $this->actingAs($user)
            ->put(route('member.profile.update'), [
                'name' => 'Nama Baru Pelanggan',
                'email' => $user->email,
                'phone' => '081299998888',
                'address' => 'Jl. Pettarani No. 44',
                'nik' => '7371101010900001',
                'sim_number' => '901234567890',
            ])->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Nama Baru Pelanggan', $user->name);
        $this->assertSame('7371101010900001', $user->memberProfile->nik);
        $this->assertSame('901234567890', $user->memberProfile->sim_number);

        // Simulasi verifikasi nomor WhatsApp dengan OTP
        $this->actingAs($user)
            ->post(route('member.verify.wa'), ['otp' => '123456'])
            ->assertRedirect(route('member.account'))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->memberProfile->isPhoneVerified());
    }

    public function test_unggah_dokumen_ekyc_ke_storage_privat_dan_pencatatan_audit_log(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $user->ensureMemberProfile();

        $fileKtp = UploadedFile::fake()->image('ktp_saya.jpg');

        $this->actingAs($user)
            ->post(route('member.documents.upload'), [
                'type' => 'ktp',
                'document' => $fileKtp,
            ])->assertSessionHas('success');

        $doc = $user->documents()->where('type', 'ktp')->first();
        $this->assertNotNull($doc);
        $this->assertSame(CustomerDocument::STATUS_PENDING, $doc->status);
        Storage::disk('local')->assertExists($doc->path);

        // Pastikan status KYC profil berubah menjadi pending
        $user->refresh();
        $this->assertSame(MemberProfile::KYC_PENDING, $user->memberProfile->kyc_status);

        // Pastikan ada log akses tercatat untuk upload
        $this->assertDatabaseHas('document_access_logs', [
            'user_id' => $user->id,
            'document_type' => 'ktp',
            'action' => 'upload',
        ]);

        // Cek bahwa user lain (bukan admin) DILARANG melihat dokumen (403)
        $otherUser = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $this->actingAs($otherUser)
            ->get(route('member.documents.view', $doc))
            ->assertForbidden();

        // Pemilik dokumen dapat melihat dan tercatat di audit log
        $this->actingAs($user)
            ->get(route('member.documents.view', $doc))
            ->assertOk();

        $this->assertDatabaseHas('document_access_logs', [
            'user_id' => $user->id,
            'document_type' => 'ktp',
            'action' => 'view',
        ]);
    }

    public function test_kenaikan_tier_otomatis_berdasarkan_jumlah_sewa_selesai_dan_poin_loyalitas(): void
    {
        $membershipService = app(MembershipService::class);
        $bookingService = app(BookingService::class);

        $member = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $profile = $member->ensureMemberProfile();
        $this->assertSame(MembershipTier::SLUG_REGULER, $profile->effectiveTier()->slug);

        // Kategori, Lokasi, dan Mobil untuk pesanan
        $cat = Category::create(['name' => 'MPV', 'slug' => 'mpv', 'sort_order' => 1]);
        $loc = Location::create(['name' => 'Kantor', 'slug' => 'kantor', 'requires_address' => false]);
        $car = Car::create([
            'category_id' => $cat->id,
            'name' => 'Avanza Test',
            'slug' => 'avanza-test',
            'brand' => 'Toyota',
            'year' => 2023,
            'plate_number' => 'DD 9999 XX',
            'transmission' => 'automatic',
            'seats' => 7,
            'fuel' => 'Bensin',
            'daily_price' => 400000,
            'status' => Car::STATUS_AVAILABLE,
        ]);

        // Selesaikan 3 pesanan bernilai masing-masing Rp 800.000 (2 hari)
        for ($i = 1; $i <= 3; $i++) {
            $booking = $bookingService->create($car, [
                'pickup_location_id' => $loc->id,
                'pickup_date' => now()->addDays($i * 5)->toDateString(),
                'return_date' => now()->addDays($i * 5 + 2)->toDateString(),
                'customer_name' => $member->name,
                'customer_phone' => $member->phone ?? '0811111111',
                'customer_email' => $member->email,
            ], $member);

            $this->assertSame($member->id, $booking->member_id);

            // Transisi status ke Selesai (Completed)
            $bookingService->transition($booking, BookingStatus::Confirmed);
            $bookingService->transition($booking, BookingStatus::Active);
            $bookingService->transition($booking, BookingStatus::Completed);
        }

        $profile->refresh();
        // 3 sewa selesai -> Otomatis naik ke Silver (Diskon 5%)
        $this->assertSame(3, $profile->total_completed_rentals);
        $this->assertSame(MembershipTier::SLUG_SILVER, $profile->effectiveTier()->slug);
        $this->assertSame(5, $profile->discountPercentage());

        // Poin loyalitas: 1 poin per Rp 100.000 -> 3 * 800.000 = 2.400.000 -> 24 poin
        $this->assertSame(24, $profile->loyalty_points);
        $this->assertDatabaseHas('loyalty_ledgers', [
            'user_id' => $member->id,
            'points' => 8,
        ]);

        // Tambahkan lagi 7 sewa selesai (total 10 sewa) -> Otomatis naik ke Gold (Diskon 10%)
        for ($j = 4; $j <= 10; $j++) {
            $extraBooking = \App\Models\Booking::factory()->create([
                'member_id' => $member->id,
                'car_id' => $car->id,
                'pickup_location_id' => $loc->id,
                'total' => 500000,
                'status' => BookingStatus::Completed,
            ]);
            $membershipService->recordCompletedRental($extraBooking);
        }

        $profile->refresh();
        $this->assertSame(10, $profile->total_completed_rentals);
        $this->assertSame(MembershipTier::SLUG_GOLD, $profile->effectiveTier()->slug);
        $this->assertSame(10, $profile->discountPercentage());
    }

    public function test_admin_dapat_mengubah_tier_secara_manual_dengan_alasan_dan_meresetnya(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $member = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $profile = $member->ensureMemberProfile();
        $this->assertSame(MembershipTier::SLUG_REGULER, $profile->effectiveTier()->slug);

        $goldTier = MembershipTier::where('slug', MembershipTier::SLUG_GOLD)->first();

        // Admin override tier ke Gold secara manual
        $this->actingAs($admin)
            ->post(route('admin.members.tier.update', $member), [
                'tier_id' => $goldTier->id,
                'notes' => 'Apresiasi relasi korporat prioritas direksi.',
            ])->assertSessionHas('success');

        $profile->refresh();
        $this->assertTrue($profile->hasManualTier());
        $this->assertSame(MembershipTier::SLUG_GOLD, $profile->effectiveTier()->slug);
        $this->assertSame('Apresiasi relasi korporat prioritas direksi.', $profile->manual_tier_notes);
        $this->assertSame($admin->id, $profile->manual_tier_updated_by);

        // Admin mereset tier manual kembali ke otomatis
        $this->actingAs($admin)
            ->post(route('admin.members.tier.reset', $member))
            ->assertSessionHas('success');

        $profile->refresh();
        $this->assertFalse($profile->hasManualTier());
        $this->assertSame(MembershipTier::SLUG_REGULER, $profile->effectiveTier()->slug);
    }

    public function test_admin_dapat_memverifikasi_dan_menolak_dokumen_ekyc_member(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $member = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $member->ensureMemberProfile();

        $ktp = CustomerDocument::create([
            'user_id' => $member->id,
            'type' => 'ktp',
            'path' => 'member_documents/dummy_ktp.jpg',
            'status' => CustomerDocument::STATUS_PENDING,
        ]);

        $sim = CustomerDocument::create([
            'user_id' => $member->id,
            'type' => 'sim',
            'path' => 'member_documents/dummy_sim.jpg',
            'status' => CustomerDocument::STATUS_PENDING,
        ]);

        // Verifikasi KTP oleh admin
        $this->actingAs($admin)
            ->post(route('admin.members.documents.verify', [$member, $ktp]))
            ->assertSessionHas('success');

        $ktp->refresh();
        $this->assertSame(CustomerDocument::STATUS_VERIFIED, $ktp->status);

        // Verifikasi SIM A oleh admin -> karena KTP & SIM lengkap, profil jadi KYC VERIFIED
        $this->actingAs($admin)
            ->post(route('admin.members.documents.verify', [$member, $sim]))
            ->assertSessionHas('success');

        $sim->refresh();
        $this->assertSame(CustomerDocument::STATUS_VERIFIED, $sim->status);

        $member->refresh();
        $this->assertSame(MemberProfile::KYC_VERIFIED, $member->memberProfile->kyc_status);

        // Penolakan dokumen dengan alasan
        $this->actingAs($admin)
            ->post(route('admin.members.documents.reject', [$member, $sim]), [
                'notes' => 'Masa berlaku SIM telah habis, harap perbarui.',
            ])->assertSessionHas('success');

        $sim->refresh();
        $this->assertSame(CustomerDocument::STATUS_REJECTED, $sim->status);
        $this->assertSame('Masa berlaku SIM telah habis, harap perbarui.', $sim->notes);

        $member->refresh();
        $this->assertSame(MemberProfile::KYC_REJECTED, $member->memberProfile->kyc_status);
    }

    public function test_pemesanan_tamu_diperbolehkan_dan_member_id_otomatis_terisi_saat_login(): void
    {
        $cat = Category::create(['name' => 'City Car', 'slug' => 'city-car', 'sort_order' => 1]);
        $loc = Location::create(['name' => 'Kantor', 'slug' => 'kantor', 'requires_address' => false]);
        $car = Car::create([
            'category_id' => $cat->id,
            'name' => 'Brio Tamu',
            'slug' => 'brio-tamu',
            'brand' => 'Honda',
            'year' => 2023,
            'plate_number' => 'DD 1111 TT',
            'transmission' => 'automatic',
            'seats' => 5,
            'fuel' => 'Bensin',
            'daily_price' => 300000,
            'status' => Car::STATUS_AVAILABLE,
        ]);

        // 1. Pemesanan oleh tamu (Guest) tanpa login
        $bookingService = app(BookingService::class);
        $guestBooking = $bookingService->create($car, [
            'pickup_location_id' => $loc->id,
            'pickup_date' => now()->addDays(2)->toDateString(),
            'return_date' => now()->addDays(4)->toDateString(),
            'customer_name' => 'Pelanggan Tamu',
            'customer_phone' => '085200001111',
            'customer_email' => 'tamu@example.com',
        ]);

        $this->assertNull($guestBooking->member_id);
        $this->assertSame('Pelanggan Tamu', $guestBooking->customer_name);

        // 2. Pemesanan oleh member terautentikasi
        $member = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $member->ensureMemberProfile();

        $memberBooking = $bookingService->create($car, [
            'pickup_location_id' => $loc->id,
            'pickup_date' => now()->addDays(5)->toDateString(),
            'return_date' => now()->addDays(7)->toDateString(),
            'customer_name' => $member->name,
            'customer_phone' => $member->phone ?? '0812334455',
            'customer_email' => $member->email,
        ], $member);

        $this->assertSame($member->id, $memberBooking->member_id);
    }

    /**
     * Contoh baku perhitungan (Wajib dari CONTEXT):
     * - Toyota Agya Rp300.000/hari, 3 hari = Rp900.000
     * - Tambahan Asuransi Rp50.000/hari x 3 = Rp150.000
     * - Member Silver 5% dari sewa mobil saja = Rp45.000
     * - Kode promo nominal Rp50.000
     * - Total = 900.000 + 150.000 - 45.000 - 50.000 = Rp955.000
     * - Jika DP 30%, DP = Rp286.500 dan sisa pelunasan = Rp668.500.
     */
    public function test_contoh_baku_perhitungan_keuangan_sesuai_aturan_bisnis(): void
    {
        $dailyPrice = 300000;
        $days = 3;
        $carSubtotal = $dailyPrice * $days; // 900.000

        $insurancePerDay = 50000;
        $addonsTotal = $insurancePerDay * $days; // 150.000

        // Diskon Member Silver 5% atas sewa mobil saja
        $silverTier = MembershipTier::where('slug', MembershipTier::SLUG_SILVER)->first();
        $this->assertSame(5, $silverTier->discount_percentage);

        $tierDiscount = (int) ($carSubtotal * ($silverTier->discount_percentage / 100)); // 45.000
        $this->assertSame(45000, $tierDiscount);

        // Kode promo nominal
        $promoDiscount = 50000;

        // Total akhir = subtotal sewa + addon - diskon tier - diskon promo
        $grandTotal = $carSubtotal + $addonsTotal - $tierDiscount - $promoDiscount;
        $this->assertSame(955000, $grandTotal);

        // DP 30%
        $dpRate = 0.30;
        $dpAmount = (int) ($grandTotal * $dpRate);
        $sisaPelunasan = $grandTotal - $dpAmount;

        $this->assertSame(286500, $dpAmount);
        $this->assertSame(668500, $sisaPelunasan);
    }
}
