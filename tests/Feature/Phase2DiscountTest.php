<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Category;
use App\Models\Location;
use App\Models\MemberProfile;
use App\Models\MembershipTier;
use App\Models\Promotion;
use App\Models\PromotionRedemption;
use App\Models\User;
use App\Services\BookingService;
use App\Services\DiscountService;
use App\Services\PricingService;
use Database\Seeders\MembershipTierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2DiscountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MembershipTierSeeder::class);
    }

    public function test_admin_dapat_mengelola_crud_promo_dan_melihat_laporan(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // 1. Admin melihat index promo
        $this->actingAs($admin)
            ->get(route('admin.promotions.index'))
            ->assertOk()
            ->assertSee('Manajemen Diskon & Promo');

        // 2. Admin membuat promo kupon baru
        $this->actingAs($admin)
            ->post(route('admin.promotions.store'), [
                'name' => 'Diskon Lebaran 2026',
                'code' => 'LEBARAN2026',
                'target_type' => Promotion::TARGET_COUPON,
                'type' => Promotion::TYPE_PERCENTAGE,
                'value' => 20,
                'max_discount' => 200000,
                'min_order_amount' => 500000,
                'min_days' => 2,
                'usage_limit' => 50,
                'usage_limit_per_user' => 1,
                'is_combinable' => 1,
                'is_active' => 1,
                'description' => 'Promo mudik lebaran diskon 20%.',
            ])->assertRedirect(route('admin.promotions.index'))
            ->assertSessionHas('success');

        $promo = Promotion::where('code', 'LEBARAN2026')->first();
        $this->assertNotNull($promo);
        $this->assertSame(20, $promo->value);
        $this->assertSame(200000, $promo->max_discount);

        // 3. Admin mengubah promo
        $this->actingAs($admin)
            ->put(route('admin.promotions.update', $promo), [
                'name' => 'Diskon Mudik Idul Fitri',
                'code' => 'MUDIK2026',
                'target_type' => Promotion::TARGET_COUPON,
                'type' => Promotion::TYPE_PERCENTAGE,
                'value' => 25,
                'max_discount' => 250000,
                'min_order_amount' => 500000,
                'min_days' => 2,
                'is_combinable' => 1,
                'is_active' => 1,
            ])->assertRedirect(route('admin.promotions.index'))
            ->assertSessionHas('success');

        $promo->refresh();
        $this->assertSame('MUDIK2026', $promo->code);
        $this->assertSame(25, $promo->value);

        // 4. Admin melihat laporan pemakaian promo
        $this->actingAs($admin)
            ->get(route('admin.promotions.report'))
            ->assertOk()
            ->assertSee('Laporan Pemakaian Diskon & Promo');

        // 5. Admin menghapus promo
        $this->actingAs($admin)
            ->delete(route('admin.promotions.destroy', $promo))
            ->assertRedirect(route('admin.promotions.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('promotions', ['id' => $promo->id]);
    }

    public function test_validasi_kode_promo_tidak_aktif_atau_kadaluwarsa(): void
    {
        $discountService = app(DiscountService::class);
        $cat = Category::create(['name' => 'City Car', 'slug' => 'city-car', 'sort_order' => 1]);
        $car = Car::create([
            'category_id' => $cat->id,
            'name' => 'Brio Test',
            'slug' => 'brio-test',
            'brand' => 'Honda',
            'year' => 2023,
            'plate_number' => 'DD 1111 XX',
            'daily_price' => 300000,
            'status' => Car::STATUS_AVAILABLE,
        ]);

        // Promo tidak aktif
        Promotion::create([
            'name' => 'Promo Nonaktif',
            'code' => 'MATI10',
            'target_type' => Promotion::TARGET_COUPON,
            'type' => Promotion::TYPE_PERCENTAGE,
            'value' => 10,
            'is_active' => false,
        ]);

        $res1 = $discountService->validateCoupon('MATI10', $car, 2, 600000);
        $this->assertFalse($res1['valid']);
        $this->assertStringContainsString('tidak aktif', $res1['message']);

        // Promo kadaluwarsa
        Promotion::create([
            'name' => 'Promo Kadaluwarsa',
            'code' => 'BASI10',
            'target_type' => Promotion::TARGET_COUPON,
            'type' => Promotion::TYPE_PERCENTAGE,
            'value' => 10,
            'is_active' => true,
            'ends_at' => now()->subDay(),
        ]);

        $res2 = $discountService->validateCoupon('BASI10', $car, 2, 600000);
        $this->assertFalse($res2['valid']);
        $this->assertStringContainsString('telah berakhir', $res2['message']);
    }

    public function test_validasi_kuota_habis_dan_kuota_per_pengguna(): void
    {
        $discountService = app(DiscountService::class);
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $cat = Category::create(['name' => 'City Car', 'slug' => 'city-car', 'sort_order' => 1]);
        $loc = Location::create(['name' => 'Kantor', 'slug' => 'kantor', 'requires_address' => false]);
        $car = Car::create([
            'category_id' => $cat->id,
            'name' => 'Agya Test',
            'slug' => 'agya-test',
            'brand' => 'Toyota',
            'year' => 2023,
            'plate_number' => 'DD 2222 XX',
            'daily_price' => 300000,
            'status' => Car::STATUS_AVAILABLE,
        ]);

        $promo = Promotion::create([
            'name' => 'Promo Terbatas',
            'code' => 'LIMIT1X',
            'target_type' => Promotion::TARGET_COUPON,
            'type' => Promotion::TYPE_NOMINAL,
            'value' => 50000,
            'usage_limit' => 2,
            'usage_limit_per_user' => 1,
            'is_active' => true,
        ]);

        // Penggunaan pertama oleh $user -> Valid
        $check1 = $discountService->validateCoupon('LIMIT1X', $car, 2, 600000, $user);
        $this->assertTrue($check1['valid']);

        // Simulasikan $user sudah menggunakan kupon ini dalam booking
        $booking1 = Booking::factory()->create(['user_id' => $user->id, 'member_id' => $user->id, 'promo_code' => 'LIMIT1X', 'promo_discount' => 50000]);
        PromotionRedemption::create([
            'promotion_id' => $promo->id,
            'booking_id' => $booking1->id,
            'user_id' => $user->id,
            'discount_amount' => 50000,
        ]);
        $promo->increment('used_count');

        // Penggunaan kedua oleh $user yang sama -> Ditolak karena melebihi kuota per user
        $check2 = $discountService->validateCoupon('LIMIT1X', $car, 2, 600000, $user);
        $this->assertFalse($check2['valid']);
        $this->assertStringContainsString('batas maksimal pemakaian kuota', $check2['message']);

        // Penggunaan oleh user lain ($user2) -> Valid karena kuota global masih tersisa 1
        $user2 = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $check3 = $discountService->validateCoupon('LIMIT1X', $car, 2, 600000, $user2);
        $this->assertTrue($check3['valid']);

        // Simulasikan $user2 memakai kuota terakhir
        $booking2 = Booking::factory()->create(['user_id' => $user2->id, 'member_id' => $user2->id, 'promo_code' => 'LIMIT1X', 'promo_discount' => 50000]);
        PromotionRedemption::create([
            'promotion_id' => $promo->id,
            'booking_id' => $booking2->id,
            'user_id' => $user2->id,
            'discount_amount' => 50000,
        ]);
        $promo->increment('used_count');

        // Penggunaan oleh user ketiga ($user3) -> Ditolak karena kuota global (2) sudah habis
        $user3 = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $check4 = $discountService->validateCoupon('LIMIT1X', $car, 2, 600000, $user3);
        $this->assertFalse($check4['valid']);
        $this->assertStringContainsString('Kuota pemakaian kode promo ini sudah habis', $check4['message']);
    }

    public function test_validasi_cakupan_kategori_dan_minimal_transaksi(): void
    {
        $discountService = app(DiscountService::class);
        $catSUV = Category::create(['name' => 'SUV', 'slug' => 'suv', 'sort_order' => 1]);
        $catCity = Category::create(['name' => 'City Car', 'slug' => 'city-car', 'sort_order' => 2]);

        $carCity = Car::create([
            'category_id' => $catCity->id,
            'name' => 'Brio',
            'slug' => 'brio',
            'brand' => 'Honda',
            'year' => 2023,
            'plate_number' => 'DD 1234 CC',
            'daily_price' => 300000,
            'status' => Car::STATUS_AVAILABLE,
        ]);

        $carSUV = Car::create([
            'category_id' => $catSUV->id,
            'name' => 'Fortuner',
            'slug' => 'fortuner',
            'brand' => 'Toyota',
            'year' => 2023,
            'plate_number' => 'DD 5678 SUV',
            'daily_price' => 1200000,
            'status' => Car::STATUS_AVAILABLE,
        ]);

        $promoSUV = Promotion::create([
            'name' => 'Promo Khusus SUV',
            'code' => 'SUVSPECIAL',
            'target_type' => Promotion::TARGET_COUPON,
            'type' => Promotion::TYPE_NOMINAL,
            'value' => 100000,
            'category_id' => $catSUV->id,
            'min_order_amount' => 1000000,
            'min_days' => 2,
            'is_active' => true,
        ]);

        // 1. Coba gunakan untuk mobil City Car -> Ditolak karena beda kategori
        $res1 = $discountService->validateCoupon('SUVSPECIAL', $carCity, 3, 900000);
        $this->assertFalse($res1['valid']);
        $this->assertStringContainsString('kategori mobil SUV', $res1['message']);

        // 2. Coba untuk mobil SUV tapi hanya 1 hari (syarat min 2 hari) -> Ditolak
        $res2 = $discountService->validateCoupon('SUVSPECIAL', $carSUV, 1, 1200000);
        $this->assertFalse($res2['valid']);
        $this->assertStringContainsString('minimal 2 hari', $res2['message']);

        // 3. Mobil SUV, 2 hari sewa (Rp 2.400.000) -> Valid
        $res3 = $discountService->validateCoupon('SUVSPECIAL', $carSUV, 2, 2400000);
        $this->assertTrue($res3['valid']);
    }

    public function test_promo_pelanggan_baru_ditolak_jika_sudah_pernah_sewa(): void
    {
        $discountService = app(DiscountService::class);
        $cat = Category::create(['name' => 'City Car', 'slug' => 'city-car', 'sort_order' => 1]);
        $car = Car::create([
            'category_id' => $cat->id,
            'name' => 'Agya',
            'slug' => 'agya',
            'brand' => 'Toyota',
            'year' => 2023,
            'plate_number' => 'DD 3333 AA',
            'daily_price' => 300000,
            'status' => Car::STATUS_AVAILABLE,
        ]);

        Promotion::create([
            'name' => 'Diskon First User',
            'code' => 'FIRSTTIMER',
            'target_type' => Promotion::TARGET_FIRST_ORDER,
            'type' => Promotion::TYPE_PERCENTAGE,
            'value' => 15,
            'is_active' => true,
        ]);

        $newUser = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        // Pengguna baru tanpa riwayat sewa -> Valid
        $check1 = $discountService->validateCoupon('FIRSTTIMER', $car, 2, 600000, $newUser);
        $this->assertTrue($check1['valid']);

        // Buat 1 booking untuk $newUser
        Booking::factory()->create(['user_id' => $newUser->id, 'member_id' => $newUser->id]);

        // Sekarang pengguna sudah punya riwayat sewa -> Ditolak
        $check2 = $discountService->validateCoupon('FIRSTTIMER', $car, 2, 600000, $newUser);
        $this->assertFalse($check2['valid']);
        $this->assertStringContainsString('penyewa baru', $check2['message']);
    }

    /**
     * Contoh Baku Perhitungan Sesuai Spesifikasi CONTEXT:
     * - Toyota Agya Rp300.000 per hari, 3 hari = Rp900.000;
     * - Tambahan Asuransi Rp50.000 per hari x 3 = Rp150.000;
     * - Member Silver 5% dari sewa mobil = Rp45.000;
     * - Kode promo nominal Rp50.000;
     * - Total = 900.000 + 150.000 - 45.000 - 50.000 = Rp955.000.
     * - Jika DP 30%, DP = Rp286.500 dan sisa pelunasan = Rp668.500.
     */
    public function test_kalkulasi_baku_harga_sewa_dan_penggabungan_diskon_tier_dengan_promo(): void
    {
        $pricingService = app(PricingService::class);
        $bookingService = app(BookingService::class);

        // Buat member dengan Tier Silver (5%)
        $silverTier = MembershipTier::where('slug', MembershipTier::SLUG_SILVER)->first();
        $member = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $profile = $member->ensureMemberProfile();
        $profile->update([
            'membership_tier_id' => $silverTier->id,
            'total_completed_rentals' => 3,
        ]);

        $cat = Category::create(['name' => 'City Car', 'slug' => 'city-car', 'sort_order' => 1]);
        $loc = Location::create(['name' => 'Kantor', 'slug' => 'kantor', 'requires_address' => false]);
        $car = Car::create([
            'category_id' => $cat->id,
            'name' => 'Toyota Agya',
            'slug' => 'toyota-agya',
            'brand' => 'Toyota',
            'year' => 2022,
            'plate_number' => 'DD 1201 PA',
            'daily_price' => 300000,
            'status' => Car::STATUS_AVAILABLE,
        ]);

        $insurance = Addon::create([
            'code' => 'insurance',
            'name' => 'Asuransi',
            'price' => 50000,
            'charge_type' => Addon::PER_DAY,
            'is_active' => true,
        ]);

        // Buat kode promo nominal Rp 50.000
        Promotion::create([
            'name' => 'Promo Potongan 50K',
            'code' => 'PADAIDI50K',
            'target_type' => Promotion::TARGET_COUPON,
            'type' => Promotion::TYPE_NOMINAL,
            'value' => 50000,
            'is_combinable' => true,
            'is_active' => true,
        ]);

        $pickup = now()->addDays(3)->toDateString();
        $return = now()->addDays(6)->toDateString(); // 3 hari sewa

        // 1. Uji perhitungan melalui PricingService::quote()
        $quote = $pricingService->quote(
            $car,
            $pickup,
            $return,
            [$insurance],
            0,
            $member,
            'PADAIDI50K',
            $member->phone,
            $loc
        );

        $this->assertSame(3, $quote['days']);
        $this->assertSame(900000, $quote['base_total']);
        $this->assertSame(150000, $quote['addons_total']);
        $this->assertSame(45000, $quote['tier_discount']);
        $this->assertSame(50000, $quote['promo_discount']);
        $this->assertSame(95000, $quote['discount']); // 45.000 + 50.000
        $this->assertSame(955000, $quote['total']); // 900.000 + 150.000 - 95.000
        $this->assertSame(286500, $quote['dp_amount']); // 30% dari 955.000
        $this->assertSame(668500, $quote['settlement_amount']); // 70%

        // 2. Buat pesanan riil melalui BookingService::create()
        $booking = $bookingService->create($car, [
            'pickup_location_id' => $loc->id,
            'pickup_date' => $pickup,
            'return_date' => $return,
            'addons' => [$insurance->id],
            'promo_code' => 'PADAIDI50K',
            'customer_name' => $member->name,
            'customer_phone' => $member->phone ?? '081234567890',
            'customer_email' => $member->email,
        ], $member);

        $this->assertSame(900000, $booking->base_total);
        $this->assertSame(150000, $booking->addons_total);
        $this->assertSame(45000, $booking->tier_discount);
        $this->assertSame(50000, $booking->promo_discount);
        $this->assertSame(95000, $booking->discount);
        $this->assertSame(955000, $booking->total);
        $this->assertSame('PADAIDI50K', $booking->promo_code);
        $this->assertNotEmpty($booking->discount_snapshot);

        // 3. Pastikan tercatat ke promotion_redemptions dan used_count bertambah
        $this->assertDatabaseHas('promotion_redemptions', [
            'booking_id' => $booking->id,
            'discount_amount' => 50000,
        ]);

        $promo = Promotion::where('code', 'PADAIDI50K')->first();
        $this->assertSame(1, $promo->used_count);

        // 4. Batalkan pesanan dan pastikan redemption di-rollback
        $bookingService->transition($booking, BookingStatus::Cancelled, 'Pelanggan membatalkan');
        $promo->refresh();
        $this->assertSame(0, $promo->used_count);
        $this->assertDatabaseMissing('promotion_redemptions', ['booking_id' => $booking->id]);
    }

    public function test_diskon_tidak_melebihi_subtotal_mobil_dan_tidak_memotong_addons(): void
    {
        $cat = Category::create(['name' => 'MPV', 'slug' => 'mpv', 'sort_order' => 1]);
        $loc = Location::create(['name' => 'Kantor', 'slug' => 'kantor', 'requires_address' => false]);
        $car = Car::create([
            'category_id' => $cat->id,
            'name' => 'Avanza',
            'slug' => 'avanza',
            'brand' => 'Toyota',
            'year' => 2022,
            'plate_number' => 'DD 7777 MPV',
            'daily_price' => 300000,
            'status' => Car::STATUS_AVAILABLE,
        ]);

        $driver = Addon::create([
            'code' => 'driver',
            'name' => 'Sopir',
            'price' => 200000,
            'charge_type' => Addon::PER_DAY,
            'is_active' => true,
        ]);

        // Buat promo nominal besar Rp 1.000.000 padahal sewa mobil hanya 1 hari (Rp 300.000)
        Promotion::create([
            'name' => 'Mega Diskon 1 Juta',
            'code' => 'MEGA1JT',
            'target_type' => Promotion::TARGET_COUPON,
            'type' => Promotion::TYPE_NOMINAL,
            'value' => 1000000,
            'is_active' => true,
        ]);

        $pricingService = app(PricingService::class);
        $quote = $pricingService->quote(
            $car,
            now()->addDays(2)->toDateString(),
            now()->addDays(3)->toDateString(), // 1 hari sewa: base_total = 300.000
            [$driver], // addons_total = 200.000
            0,
            null,
            'MEGA1JT',
            null,
            $loc
        );

        // Diskon maksimal yang dipotong adalah Rp 300.000 (tidak boleh memotong addon sopir)
        $this->assertSame(300000, $quote['promo_discount']);
        $this->assertSame(300000, $quote['discount']);
        // Total akhir = base_total (300.000) + addons (200.000) - discount (300.000) = 200.000
        $this->assertSame(200000, $quote['total']);
    }

    public function test_form_review_dan_pesan_mobil_dengan_kode_promo_dan_validasi_error(): void
    {
        $cat = Category::create(['name' => 'City Car', 'slug' => 'city-car', 'sort_order' => 1]);
        $loc = Location::create(['name' => 'Kantor', 'slug' => 'kantor', 'requires_address' => false]);
        $car = Car::create([
            'category_id' => $cat->id,
            'name' => 'Agya UI Test',
            'slug' => 'agya-ui-test',
            'brand' => 'Toyota',
            'year' => 2022,
            'plate_number' => 'DD 9999 UI',
            'daily_price' => 300000,
            'status' => Car::STATUS_AVAILABLE,
        ]);

        Promotion::create([
            'name' => 'Promo Diskon 50K',
            'code' => 'HEMAT50K',
            'target_type' => Promotion::TARGET_COUPON,
            'type' => Promotion::TYPE_NOMINAL,
            'value' => 50000,
            'is_active' => true,
        ]);

        // 1. Kirim review dengan kode promo SALAH -> Redirect back dengan error
        $responseError = $this->post(route('bookings.review', $car), [
            'pickup_location_id' => $loc->id,
            'pickup_date' => now()->addDays(2)->toDateString(),
            'return_date' => now()->addDays(4)->toDateString(),
            'promo_code' => 'KODESALAH',
            'customer_name' => 'Budi Tester',
            'customer_phone' => '081234567890',
            'agree_terms' => '1',
        ]);

        $responseError->assertSessionHasErrors('promo_code');

        // 2. Kirim review dengan kode promo BENAR -> Halaman review menampilkan rincian diskon
        $responseSuccess = $this->post(route('bookings.review', $car), [
            'pickup_location_id' => $loc->id,
            'pickup_date' => now()->addDays(2)->toDateString(),
            'return_date' => now()->addDays(4)->toDateString(),
            'promo_code' => 'HEMAT50K',
            'customer_name' => 'Budi Tester',
            'customer_phone' => '081234567890',
            'agree_terms' => '1',
        ]);

        $responseSuccess->assertOk();
        $responseSuccess->assertSee('HEMAT50K');
        $responseSuccess->assertSee('−Rp 50.000');
    }
}

