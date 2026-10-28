<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\CarUnavailableException;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Location;
use App\Models\PromotionRedemption;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        protected PricingService $pricing,
        protected AvailabilityService $availability,
    ) {}

    /**
     * Tambahan yang dipakai untuk pesanan: pilihan pelanggan (hanya yang aktif)
     * ditambah "Antar-jemput" otomatis bila lokasi mewajibkan alamat antar.
     *
     * @param  array<int, int|string>  $addonIds
     * @return Collection<int, Addon>
     */
    public function resolveAddons(array $addonIds, ?Location $location): Collection
    {
        $addons = Addon::active()->whereIn('id', array_map('intval', $addonIds))->orderBy('id')->get();

        if ($location?->requires_address) {
            $delivery = Addon::active()->where('code', Addon::CODE_DELIVERY)->first();
            if ($delivery && ! $addons->contains('id', $delivery->id)) {
                $addons->push($delivery);
            }
        }

        return $addons->values();
    }

    /**
     * Membuat pesanan baru dengan aman dari pesanan ganda:
     * 1. buka transaksi database;
     * 2. kunci baris mobil (SELECT ... FOR UPDATE) sehingga permintaan lain
     *    untuk mobil yang sama harus menunggu;
     * 3. periksa ulang ketersediaan di dalam transaksi;
     * 4. hitung ulang harga di server dan simpan salinan harga (snapshot).
     *
     * @param  array<string, mixed>  $data  data tervalidasi dari StoreBookingRequest
     *
     * @throws CarUnavailableException
     */
    public function create(Car $car, array $data, ?User $user = null): Booking
    {
        return DB::transaction(function () use ($car, $data, $user) {
            /** @var Car $lockedCar */
            $lockedCar = Car::query()->whereKey($car->id)->lockForUpdate()->firstOrFail();

            if (! $this->availability->isAvailable($lockedCar, $data['pickup_date'], $data['return_date'])) {
                throw new CarUnavailableException;
            }

            $location = Location::findOrFail($data['pickup_location_id']);
            $addons = $this->resolveAddons($data['addons'] ?? [], $location);

            $userId = $user?->id ?? ($data['user_id'] ?? null);
            $memberId = $user?->id ?? ($data['member_id'] ?? $userId);
            $resolvedUser = $user ?? ($userId ? User::find($userId) : null);

            $promoCode = $data['promo_code'] ?? null;
            $phone = $data['customer_phone'] ?? null;

            $quote = $this->pricing->quote(
                $lockedCar,
                $data['pickup_date'],
                $data['return_date'],
                $addons,
                (int) ($data['discount'] ?? 0),
                $resolvedUser,
                $promoCode,
                $phone,
                $location
            );

            $booking = Booking::create([
                'code' => Booking::generateCode(),
                'user_id' => $userId,
                'member_id' => $memberId,
                'car_id' => $lockedCar->id,
                'pickup_location_id' => $location->id,
                'delivery_address' => $location->requires_address ? ($data['delivery_address'] ?? null) : null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'pickup_date' => $data['pickup_date'],
                'return_date' => $data['return_date'],
                'days' => $quote['days'],
                'daily_price' => $quote['daily_price'],
                'base_total' => $quote['base_total'],
                'addons_total' => $quote['addons_total'],
                'discount' => $quote['discount'],
                'promo_code' => $quote['promo_code'],
                'tier_discount' => $quote['tier_discount'],
                'promo_discount' => $quote['promo_discount'],
                'discount_snapshot' => $quote['discount_snapshot'],
                'total' => $quote['total'],
                'status' => BookingStatus::Pending,
                'notes' => $data['notes'] ?? null,
            ]);

            $booking->addons()->createMany($quote['addons']);

            // Catat pemakaian promo bila ada
            if ($booking->promo_discount > 0 && ! empty($booking->promo_code)) {
                app(DiscountService::class)->recordRedemption($booking);
            }

            return $booking->load(['car', 'pickupLocation', 'addons']);
        });
    }

    /**
     * Mengubah status pesanan sesuai alur yang diizinkan:
     * pending -> confirmed -> active -> completed; cancelled dari pending/confirmed.
     *
     * @throws InvalidStatusTransitionException
     */
    public function transition(Booking $booking, BookingStatus $target, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($booking, $target, $reason) {
            /** @var Booking $locked */
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if (! $locked->status->canTransitionTo($target)) {
                throw new InvalidStatusTransitionException($locked->status, $target);
            }

            $locked->status = $target;
            $now = now();
            match ($target) {
                BookingStatus::Confirmed => $locked->confirmed_at = $now,
                BookingStatus::Active => $locked->picked_up_at = $now,
                BookingStatus::Completed => $locked->completed_at = $now,
                BookingStatus::Cancelled => [$locked->cancelled_at = $now, $locked->cancel_reason = $reason],
                default => null,
            };
            $locked->save();

            if ($target === BookingStatus::Completed) {
                app(MembershipService::class)->recordCompletedRental($locked);
            }

            if ($target === BookingStatus::Cancelled) {
                // Kembalikan kuota promo jika pesanan dibatalkan
                if ($locked->promo_code) {
                    $redemption = PromotionRedemption::where('booking_id', $locked->id)->first();
                    if ($redemption) {
                        $promo = $redemption->promotion;
                        $redemption->delete();
                        if ($promo && $promo->used_count > 0) {
                            $promo->decrement('used_count');
                        }
                    }
                }
            }

            $booking->setRawAttributes($locked->getAttributes(), true);

            return $booking;
        });
    }
}
