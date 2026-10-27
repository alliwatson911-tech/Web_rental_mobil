<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingService;
use Illuminate\Console\Command;

class CancelExpiredBookings extends Command
{
    protected $signature = 'bookings:cancel-expired';

    protected $description = 'Batalkan otomatis pesanan pending yang melewati batas waktu (default 5 jam)';

    public function handle(BookingService $service): int
    {
        $hours = (int) config('padaidi.pending_timeout_hours', 5);
        $cutoff = now()->subHours($hours);

        // Pesanan yang sudah mengunggah bukti transfer (belum ditolak) TIDAK dibatalkan
        // otomatis, agar pelanggan yang sudah membayar tidak dirugikan; admin yang memutuskan.
        $bookings = Booking::query()
            ->where('status', BookingStatus::Pending->value)
            ->where('created_at', '<=', $cutoff)
            ->whereDoesntHave('payments', fn ($q) => $q->where('status', '!=', Payment::STATUS_REJECTED))
            ->get();

        foreach ($bookings as $booking) {
            $service->transition($booking, BookingStatus::Cancelled, "Dibatalkan otomatis: tidak dikonfirmasi dalam {$hours} jam.");
        }

        $this->info("{$bookings->count()} pesanan pending dibatalkan otomatis.");

        return self::SUCCESS;
    }
}
