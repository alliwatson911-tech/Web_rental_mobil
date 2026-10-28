<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Category;
use App\Models\Location;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Models\User;
use App\Models\WebhookEvent;
use Database\Seeders\MembershipTierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected Car $car;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MembershipTierSeeder::class);

        $category = Category::factory()->create(['name' => 'Sedan Phase3']);
        $this->car = Car::factory()->create([
            'category_id' => $category->id,
            'name' => 'Toyota Vios Phase3',
            'daily_price' => 400000,
            'status' => Car::STATUS_AVAILABLE,
        ]);
        $this->location = Location::factory()->create(['name' => 'Bandara Sultan Hasanuddin']);
    }

    protected function makeBooking(int $days = 3, int $dailyPrice = 400000, array $attributes = []): Booking
    {
        $baseTotal = $days * $dailyPrice;

        return Booking::create(array_merge([
            'code' => Booking::generateCode(),
            'car_id' => $this->car->id,
            'pickup_location_id' => $this->location->id,
            'customer_name' => 'Ahmad Pelanggan',
            'customer_phone' => '081299887766',
            'customer_email' => 'ahmad@example.com',
            'pickup_date' => now()->addDays(2)->toDateString(),
            'return_date' => now()->addDays(2 + $days)->toDateString(),
            'days' => $days,
            'daily_price' => $dailyPrice,
            'base_total' => $baseTotal,
            'addons_total' => 0,
            'total' => $baseTotal,
            'status' => BookingStatus::Pending,
        ], $attributes));
    }

    public function test_pelanggan_dapat_melihat_halaman_checkout_dan_memilih_metode_bayar(): void
    {
        $booking = $this->makeBooking(3, 400000); // 1.200.000

        $response = $this->withSession(['booking_access' => [$booking->code]])
            ->get(route('bookings.pay', $booking));

        $response->assertOk()
            ->assertSee('Pembayaran Online Otomatis')
            ->assertSee('Toyota Vios Phase3')
            ->assertSee('Uang Muka (DP 30%)')
            ->assertSee('Bayar Penuh (100%)')
            ->assertSee('QRIS')
            ->assertSee('BCA Virtual Account');
    }

    public function test_pelanggan_dapat_membuat_tagihan_pembayaran_dp_dan_full_dengan_nominal_server(): void
    {
        $booking = $this->makeBooking(3, 400000); // Grand total: 1.200.000

        // 1. Tagihan DP 30% via QRIS (30% dari 1.200.000 = 360.000)
        $responseDp = $this->withSession(['booking_access' => [$booking->code]])
            ->post(route('bookings.pay.process', $booking), [
                'payment_type' => 'dp',
                'channel' => 'qris',
            ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'payment_type' => 'dp',
            'channel' => 'qris',
            'amount' => 360000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $dpPayment = Payment::where('booking_id', $booking->id)->first();
        $this->assertNotNull($dpPayment->reference_id);
        $this->assertNotNull($dpPayment->qr_code_url);
        $this->assertNotNull($dpPayment->expires_at);

        $responseDp->assertRedirect(route('bookings.pay.instruction', [$booking, $dpPayment]));

        // Halaman instruksi menampilkan QRIS
        $this->withSession(['booking_access' => [$booking->code]])
            ->get(route('bookings.pay.instruction', [$booking, $dpPayment]))
            ->assertOk()
            ->assertSee('Scan Kode QRIS di Bawah Ini')
            ->assertSee(rupiah(360000));

        // 2. Tagihan Pelunasan Penuh via BCA VA
        $booking2 = $this->makeBooking(2, 500000); // 1.000.000
        $this->withSession(['booking_access' => [$booking2->code]])
            ->post(route('bookings.pay.process', $booking2), [
                'payment_type' => 'full',
                'channel' => 'va_bca',
            ]);

        $fullPayment = Payment::where('booking_id', $booking2->id)->first();
        $this->assertEquals(1000000, $fullPayment->amount);
        $this->assertEquals('va_bca', $fullPayment->channel);
        $this->assertStringStartsWith('8801', $fullPayment->va_number);

        // Halaman instruksi menampilkan nomor VA BCA
        $this->withSession(['booking_access' => [$booking2->code]])
            ->get(route('bookings.pay.instruction', [$booking2, $fullPayment]))
            ->assertOk()
            ->assertSee('Nomor BCA Virtual Account')
            ->assertSee($fullPayment->va_number);
    }

    public function test_webhook_berhasil_memperbarui_status_pembayaran_dan_konfirmasi_pesanan_otomatis(): void
    {
        $booking = $this->makeBooking(3, 400000);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'reference_id' => 'PDD-PAY-' . $booking->code . '-TEST01',
            'external_id' => 'GATEWAY-EXT-01',
            'payment_type' => 'dp',
            'channel' => 'qris',
            'amount' => 360000,
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);

        $this->assertEquals(BookingStatus::Pending, $booking->fresh()->status);

        // Simulasi pengiriman webhook dari Payment Gateway
        $webhookPayload = [
            'event_id' => 'EVT-WEBHOOK-TEST-001',
            'event' => 'payment.succeeded',
            'external_id' => $payment->reference_id,
            'id' => $payment->external_id,
            'status' => 'PAID',
            'amount' => 360000,
        ];

        $response = $this->postJson(route('payment.webhook'), $webhookPayload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'processed',
                'payment_status' => Payment::STATUS_PAID,
            ]);

        // Verifikasi state pembayaran
        $payment->refresh();
        $this->assertEquals(Payment::STATUS_PAID, $payment->status);
        $this->assertNotNull($payment->paid_at);

        // Verifikasi auto-konfirmasi pesanan
        $booking->refresh();
        $this->assertEquals(BookingStatus::Confirmed, $booking->status);

        // Verifikasi pencatatan peristiwa mentah di webhook_events
        $this->assertDatabaseHas('webhook_events', [
            'event_id' => 'EVT-WEBHOOK-TEST-001',
            'status' => WebhookEvent::STATUS_PROCESSED,
        ]);
    }

    public function test_webhook_idempoten_mencegah_pemrosesan_ganda(): void
    {
        $booking = $this->makeBooking(2, 300000);
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'reference_id' => 'PDD-PAY-' . $booking->code . '-IDEMP01',
            'payment_type' => 'full',
            'channel' => 'va_mandiri',
            'amount' => 600000,
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);

        $payload = [
            'event_id' => 'EVT-SAME-ID-999',
            'external_id' => $payment->reference_id,
            'status' => 'PAID',
            'amount' => 600000,
        ];

        // Pengiriman pertama: diproses
        $first = $this->postJson(route('payment.webhook'), $payload);
        $first->assertOk()->assertJson(['status' => 'processed']);

        // Pengiriman kedua dengan event_id yang sama persis: idempoten
        $second = $this->postJson(route('payment.webhook'), $payload);
        $second->assertOk()->assertJson([
            'success' => true,
            'status' => 'already_processed',
        ]);

        // Hanya ada 1 record webhook event dengan ID tersebut
        $this->assertEquals(1, WebhookEvent::where('event_id', 'EVT-SAME-ID-999')->count());
    }

    public function test_webhook_dengan_token_tidak_sah_ditolak_401(): void
    {
        $payload = [
            'event_id' => 'EVT-HACK-01',
            'status' => 'PAID',
            'amount' => 1000000,
        ];

        // Kirim header token yang keliru
        $response = $this->withHeaders([
            'x-callback-token' => 'token-salah-dan-palsu',
        ])->postJson(route('payment.webhook'), $payload);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'status' => 'unauthorized',
            ]);
    }

    public function test_perintah_expire_payments_menandai_transaksi_kedaluwarsa(): void
    {
        $booking = $this->makeBooking(2, 300000);

        // Buat payment pending yang batas waktunya 2 jam yang lalu
        $expiredPayment = Payment::create([
            'booking_id' => $booking->id,
            'reference_id' => 'PDD-EXP-01',
            'payment_type' => 'full',
            'channel' => 'qris',
            'amount' => 600000,
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->subHours(2),
        ]);

        // Buat payment pending yang masih aktif
        $activePayment = Payment::create([
            'booking_id' => $booking->id,
            'reference_id' => 'PDD-ACTIVE-01',
            'payment_type' => 'full',
            'channel' => 'qris',
            'amount' => 600000,
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->addHours(2),
        ]);

        $this->artisan('payments:expire')
            ->expectsOutputToContain('1 transaksi pending kedaluwarsa')
            ->assertSuccessful();

        $expiredPayment->refresh();
        $activePayment->refresh();

        $this->assertEquals(Payment::STATUS_EXPIRED, $expiredPayment->status);
        $this->assertEquals(Payment::STATUS_PENDING, $activePayment->status);
    }

    public function test_perintah_reconcile_payments_menyinkronkan_transaksi_ke_gateway(): void
    {
        $booking = $this->makeBooking(2, 300000);

        $pendingPayment = Payment::create([
            'booking_id' => $booking->id,
            'reference_id' => 'PDD-RECON-01',
            'external_id' => 'GATEWAY-RECON-EXT-01',
            'gateway_name' => 'fake',
            'payment_type' => 'full',
            'channel' => 'va_bca',
            'amount' => 600000,
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);

        $this->artisan('payments:reconcile')
            ->expectsOutputToContain('1 transaksi berhasil disinkronkan')
            ->assertSuccessful();

        $pendingPayment->refresh();
        $this->assertEquals(Payment::STATUS_PAID, $pendingPayment->status);
        $this->assertEquals(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    public function test_admin_dapat_memproses_refund_transaksi_dan_ekspor_csv(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $booking = $this->makeBooking(2, 400000);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'reference_id' => 'PDD-REFUND-01',
            'gateway_name' => 'fake',
            'payment_type' => 'full',
            'channel' => 'va_bca',
            'amount' => 800000,
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
        ]);

        // 1. Admin melihat dasbor pembayaran dan metrik
        $this->actingAs($admin)
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('Transaksi & Pembayaran')
            ->assertSee('PDD-REFUND-01')
            ->assertSee(rupiah(800000));

        // 2. Refund sebagian (300.000)
        $this->actingAs($admin)
            ->post(route('admin.payments.refund', $payment), [
                'amount' => 300000,
                'reason' => 'Perubahan jadwal sewa pelanggan',
            ])
            ->assertRedirect();

        $payment->refresh();
        $this->assertEquals(Payment::STATUS_PARTIALLY_REFUNDED, $payment->status);
        $this->assertEquals(500000, $payment->refundableAmount());

        $this->assertDatabaseHas('payment_refunds', [
            'payment_id' => $payment->id,
            'amount' => 300000,
            'reason' => 'Perubahan jadwal sewa pelanggan',
            'status' => PaymentRefund::STATUS_COMPLETED,
        ]);

        // 3. Refund sisa (500.000) hingga lunas ter-refund
        $this->actingAs($admin)
            ->post(route('admin.payments.refund', $payment), [
                'amount' => 500000,
                'reason' => 'Pelanggan membatalkan sisa sewa',
            ])
            ->assertRedirect();

        $payment->refresh();
        $this->assertEquals(Payment::STATUS_REFUNDED, $payment->status);
        $this->assertEquals(0, $payment->refundableAmount());

        // 4. Refund melebihi batas saldo ditolak
        $this->actingAs($admin)
            ->post(route('admin.payments.refund', $payment), [
                'amount' => 100000,
                'reason' => 'Kelebihan refund',
            ])
            ->assertSessionHasErrors(['refund']);

        // 5. Ekspor data ke CSV
        $exportResponse = $this->actingAs($admin)
            ->get(route('admin.payments.export'));

        $exportResponse->assertOk();
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_pelanggan_dan_admin_dapat_melihat_invoice_resmi_pesanan(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $booking = $this->makeBooking(3, 400000);

        Payment::create([
            'booking_id' => $booking->id,
            'reference_id' => 'PDD-INV-TEST-01',
            'gateway_name' => 'fake',
            'payment_type' => 'full',
            'channel' => 'qris',
            'amount' => 1200000,
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
        ]);

        // 1. Pengunjung asing tanpa hak akses ditolak 403
        $this->get(route('bookings.invoice', $booking))
            ->assertStatus(403);

        // 2. Pelanggan dengan hak akses session melihat invoice
        $this->withSession(['booking_access' => [$booking->code]])
            ->get(route('bookings.invoice', $booking))
            ->assertOk()
            ->assertSee('FAKTUR SEWA / INVOICE')
            ->assertSee('INV-' . $booking->code)
            ->assertSee('LUNAS (PAID)')
            ->assertSee(rupiah(1200000))
            ->assertSee('Toyota Vios Phase3');

        // 3. Admin dapat melihat invoice pesanan mana pun
        $this->actingAs($admin)
            ->get(route('bookings.invoice', $booking))
            ->assertOk()
            ->assertSee('INV-' . $booking->code);
    }
}
