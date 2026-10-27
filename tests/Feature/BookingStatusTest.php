<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BookingStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_alur_lengkap_pending_confirmed_active_completed(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.bookings.confirm', $booking))->assertSessionHasNoErrors();
        $this->assertSame(BookingStatus::Confirmed, $booking->refresh()->status);
        $this->assertNotNull($booking->confirmed_at);

        $this->actingAs($this->admin)->post(route('admin.bookings.pickup', $booking))->assertSessionHasNoErrors();
        $this->assertSame(BookingStatus::Active, $booking->refresh()->status);
        $this->assertNotNull($booking->picked_up_at);

        $this->actingAs($this->admin)->post(route('admin.bookings.complete', $booking))->assertSessionHasNoErrors();
        $this->assertSame(BookingStatus::Completed, $booking->refresh()->status);
        $this->assertNotNull($booking->completed_at);
    }

    public function test_status_tidak_boleh_melompat(): void
    {
        $booking = Booking::factory()->create(); // pending

        $this->actingAs($this->admin)->post(route('admin.bookings.complete', $booking))->assertSessionHasErrors('status');
        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);

        $this->actingAs($this->admin)->post(route('admin.bookings.pickup', $booking))->assertSessionHasErrors('status');
        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
    }

    public function test_pembatalan_hanya_dari_pending_atau_confirmed(): void
    {
        $pending = Booking::factory()->create();
        $confirmed = Booking::factory()->status(BookingStatus::Confirmed)->create();
        $active = Booking::factory()->status(BookingStatus::Active)->create();
        $completed = Booking::factory()->status(BookingStatus::Completed)->create();

        $this->actingAs($this->admin)->post(route('admin.bookings.reject', $pending), ['reason' => 'Dokumen tidak lengkap'])->assertSessionHasNoErrors();
        $this->assertSame(BookingStatus::Cancelled, $pending->refresh()->status);
        $this->assertSame('Dokumen tidak lengkap', $pending->cancel_reason);

        $this->actingAs($this->admin)->post(route('admin.bookings.reject', $confirmed))->assertSessionHasNoErrors();
        $this->assertSame(BookingStatus::Cancelled, $confirmed->refresh()->status);

        $this->actingAs($this->admin)->post(route('admin.bookings.reject', $active))->assertSessionHasErrors('status');
        $this->assertSame(BookingStatus::Active, $active->refresh()->status);

        $this->actingAs($this->admin)->post(route('admin.bookings.reject', $completed))->assertSessionHasErrors('status');
        $this->assertSame(BookingStatus::Completed, $completed->refresh()->status);
    }

    public function test_status_final_tidak_bisa_diubah_lagi(): void
    {
        $cancelled = Booking::factory()->status(BookingStatus::Cancelled)->create();
        $this->expectException(InvalidStatusTransitionException::class);
        app(BookingService::class)->transition($cancelled, BookingStatus::Confirmed);
    }

    public function test_tabel_transisi_status_sesuai_aturan(): void
    {
        $allowed = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['active', 'cancelled'],
            'active' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];

        foreach (BookingStatus::cases() as $from) {
            foreach (BookingStatus::cases() as $to) {
                $this->assertSame(
                    in_array($to->value, $allowed[$from->value], true),
                    $from->canTransitionTo($to),
                    "Transisi {$from->value} -> {$to->value}"
                );
            }
        }
    }

    public function test_hanya_admin_yang_bisa_mengubah_status(): void
    {
        $booking = Booking::factory()->create();
        $customer = User::factory()->create();

        $this->post(route('admin.bookings.confirm', $booking))->assertRedirect(route('admin.login'));
        $this->actingAs($customer)->post(route('admin.bookings.confirm', $booking))->assertForbidden();
        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
    }

    public function test_pending_lebih_dari_5_jam_dibatalkan_otomatis(): void
    {
        $old = Booking::factory()->create();
        $old->forceFill(['created_at' => now()->subHours(5)->subMinute()])->save();

        $fresh = Booking::factory()->create();
        $fresh->forceFill(['created_at' => now()->subHours(4)])->save();

        $oldConfirmed = Booking::factory()->status(BookingStatus::Confirmed)->create();
        $oldConfirmed->forceFill(['created_at' => now()->subHours(10)])->save();

        $oldWithProof = Booking::factory()->create();
        $oldWithProof->forceFill(['created_at' => now()->subHours(6)])->save();
        $oldWithProof->payments()->create(['amount' => 100000, 'method' => 'transfer', 'status' => Payment::STATUS_PENDING]);

        Artisan::call('bookings:cancel-expired');

        $this->assertSame(BookingStatus::Cancelled, $old->refresh()->status);
        $this->assertNotNull($old->cancel_reason);
        $this->assertSame(BookingStatus::Pending, $fresh->refresh()->status);
        $this->assertSame(BookingStatus::Confirmed, $oldConfirmed->refresh()->status);
        $this->assertSame(BookingStatus::Pending, $oldWithProof->refresh()->status);
    }

    public function test_admin_mencatat_dan_memverifikasi_pembayaran(): void
    {
        $booking = Booking::factory()->create(['total' => 700000]);
        $proof = $booking->payments()->create(['amount' => 200000, 'method' => 'transfer', 'status' => Payment::STATUS_PENDING]);

        $this->actingAs($this->admin)->post(route('admin.payments.verify', $proof))->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post(route('admin.payments.store', $booking), [
            'amount' => 500000, 'method' => 'tunai', 'paid_at' => now()->format('Y-m-d H:i'),
        ])->assertSessionHasNoErrors();

        $booking->refresh()->load('payments');
        $this->assertSame(700000, $booking->paidAmount());
        $this->assertTrue($booking->isPaidOff());
        $this->assertSame($this->admin->id, $proof->refresh()->verified_by);
    }
}
