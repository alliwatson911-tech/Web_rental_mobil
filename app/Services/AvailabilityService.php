<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Car;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Aturan ketersediaan:
 * - mobil berstatus "available";
 * - tidak ada pesanan aktif (pending/confirmed/active) yang beririsan dengan [ambil, kembali);
 * - tidak ada jadwal servis (car_blocks, tanggal inklusif) yang beririsan.
 *
 * Karena rentang pesanan [ambil, kembali), mobil yang kembali tanggal X
 * boleh diambil pelanggan lain pada tanggal X.
 */
class AvailabilityService
{
    /** Query mobil yang tersedia pada rentang tanggal tertentu. */
    public function availableCarsQuery(CarbonInterface|string $pickup, CarbonInterface|string $return): Builder
    {
        [$p, $r] = $this->normalize($pickup, $return);

        return Car::query()
            ->where('status', Car::STATUS_AVAILABLE)
            ->whereDoesntHave('bookings', fn (Builder $q) => $q->blocking()->overlapping($p, $r))
            ->whereDoesntHave('blocks', fn (Builder $q) => $this->overlappingBlocks($q, $p, $r));
    }

    public function isAvailable(Car $car, CarbonInterface|string $pickup, CarbonInterface|string $return, ?int $ignoreBookingId = null): bool
    {
        [$p, $r] = $this->normalize($pickup, $return);

        if ($car->status !== Car::STATUS_AVAILABLE) {
            return false;
        }

        $hasBooking = Booking::query()
            ->where('car_id', $car->id)
            ->blocking()
            ->overlapping($p, $r)
            ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
            ->exists();

        if ($hasBooking) {
            return false;
        }

        return ! $this->overlappingBlocks($car->blocks()->getQuery(), $p, $r)->exists();
    }

    /** Jadwal servis inklusif [start, end] beririsan dengan [p, r). */
    protected function overlappingBlocks(Builder $query, string $p, string $r): Builder
    {
        return $query->where('start_date', '<', $r)->where('end_date', '>=', $p);
    }

    /** @return array{0:string,1:string} */
    protected function normalize(CarbonInterface|string $pickup, CarbonInterface|string $return): array
    {
        $p = CarbonImmutable::parse($pickup)->startOfDay();
        $r = CarbonImmutable::parse($return)->startOfDay();
        if ($r->lessThanOrEqualTo($p)) {
            $r = $p->addDay(); // minimal 1 hari
        }

        return [$p->toDateString(), $r->toDateString()];
    }
}
