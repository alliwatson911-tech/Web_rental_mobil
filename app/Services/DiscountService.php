<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Location;
use App\Models\Promotion;
use App\Models\PromotionRedemption;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DiscountService
{
    /**
     * Memvalidasi apakah kode promo dapat digunakan untuk pesanan tertentu.
     *
     * @return array{valid: bool, promotion: ?Promotion, message: ?string}
     */
    public function validateCoupon(
        string $code,
        Car $car,
        int $days,
        int $carSubtotal,
        ?User $user = null,
        ?string $phone = null,
        ?Location $location = null
    ): array {
        $cleanCode = strtoupper(trim($code));
        $promo = Promotion::where('code', $cleanCode)->first();

        if (! $promo || ! $promo->is_active) {
            return [
                'valid' => false,
                'promotion' => null,
                'message' => 'Kode promo tidak valid atau sudah tidak aktif.',
            ];
        }

        if (! $promo->isStarted()) {
            return [
                'valid' => false,
                'promotion' => null,
                'message' => 'Kode promo ini baru dapat digunakan mulai ' . $promo->starts_at->translatedFormat('d M Y H:i') . '.',
            ];
        }

        if ($promo->isExpired()) {
            return [
                'valid' => false,
                'promotion' => null,
                'message' => 'Masa berlaku kode promo ini telah berakhir pada ' . $promo->ends_at->translatedFormat('d M Y H:i') . '.',
            ];
        }

        if (! $promo->isQuotaAvailable()) {
            return [
                'valid' => false,
                'promotion' => null,
                'message' => 'Kuota pemakaian kode promo ini sudah habis.',
            ];
        }

        if (! $promo->isUserQuotaAvailable($user, $phone)) {
            return [
                'valid' => false,
                'promotion' => null,
                'message' => 'Anda telah mencapai batas maksimal pemakaian kuota untuk kode promo ini.',
            ];
        }

        if ($promo->category_id !== null && $car->category_id !== $promo->category_id) {
            return [
                'valid' => false,
                'promotion' => null,
                'message' => "Kode promo ini hanya berlaku untuk kategori mobil {$promo->category?->name}.",
            ];
        }

        if ($promo->car_id !== null && $car->id !== $promo->car_id) {
            return [
                'valid' => false,
                'promotion' => null,
                'message' => "Kode promo ini hanya berlaku khusus untuk mobil {$promo->car?->brand} {$promo->car?->model}.",
            ];
        }

        if ($promo->location_id !== null && $location && $location->id !== $promo->location_id) {
            return [
                'valid' => false,
                'promotion' => null,
                'message' => "Kode promo ini hanya berlaku untuk pengambilan di {$promo->location?->name}.",
            ];
        }

        if ($promo->min_days > 1 && $days < $promo->min_days) {
            return [
                'valid' => false,
                'promotion' => null,
                'message' => "Kode promo ini membutuhkan durasi sewa minimal {$promo->min_days} hari.",
            ];
        }

        if ($promo->min_order_amount > 0 && $carSubtotal < $promo->min_order_amount) {
            return [
                'valid' => false,
                'promotion' => null,
                'message' => 'Minimal biaya sewa mobil untuk promo ini adalah ' . rupiah($promo->min_order_amount) . '.',
            ];
        }

        // Cek diskon khusus pengguna baru (first_order)
        if ($promo->target_type === Promotion::TARGET_FIRST_ORDER) {
            $hasPreviousOrders = false;
            if ($user) {
                $hasPreviousOrders = Booking::where('member_id', $user->id)
                    ->orWhere('user_id', $user->id)
                    ->exists();
            } elseif ($phone) {
                $hasPreviousOrders = Booking::where('customer_phone', $phone)->exists();
            }

            if ($hasPreviousOrders) {
                return [
                    'valid' => false,
                    'promotion' => null,
                    'message' => 'Kode promo ini khusus diperuntukkan bagi penyewa baru (transaksi pertama).',
                ];
            }
        }

        return [
            'valid' => true,
            'promotion' => $promo,
            'message' => null,
        ];
    }

    /**
     * Hitung pemotongan harga sewa mobil dengan urutan penerapan yang tegas:
     * 1. Subtotal sewa mobil murni (tarif harian x hari).
     * 2. Terapkan diskon persentase terlebih dahulu (diskon tier member atau promo persentase).
     * 3. Terapkan diskon nominal (misal voucher nominal Rp 50.000).
     * 4. Total potongan diskon tidak boleh melebihi subtotal sewa mobil (maks 100%).
     * 5. Buat snapshot rincian diskon untuk integritas keuangan pesanan.
     *
     * @return array{
     *     tier_discount: int,
     *     promo_discount: int,
     *     total_discount: int,
     *     discount_snapshot: array,
     *     promotion: ?Promotion,
     *     coupon_error: ?string
     * }
     */
    public function calculate(
        Car $car,
        int $days,
        int $carSubtotal,
        ?User $user = null,
        ?string $promoCode = null,
        ?string $phone = null,
        ?Location $location = null
    ): array {
        $remainingSubtotal = $carSubtotal;
        $tierDiscount = 0;
        $promoDiscount = 0;
        $snapshot = [];
        $appliedPromotion = null;
        $couponError = null;

        // 1. Cek Diskon Tier Member (Persentase)
        $memberTier = $user?->tier();
        $tierPercentage = $memberTier ? (int) $memberTier->discount_percentage : 0;

        if ($tierPercentage > 0) {
            $tierDiscount = (int) round(($carSubtotal * $tierPercentage) / 100);
            $tierDiscount = min($tierDiscount, $remainingSubtotal);
            $remainingSubtotal = max(0, $remainingSubtotal - $tierDiscount);

            $snapshot['tier'] = [
                'name' => $memberTier->name,
                'percentage' => $tierPercentage,
                'amount' => $tierDiscount,
            ];
        }

        // 2. Cek Kode Promo (Voucher / Kupon)
        if (! empty($promoCode)) {
            $validation = $this->validateCoupon($promoCode, $car, $days, $carSubtotal, $user, $phone, $location);

            if ($validation['valid']) {
                /** @var Promotion $promo */
                $promo = $validation['promotion'];

                // Periksa apakah promo boleh digabung bila ada diskon tier
                if (! $promo->is_combinable && $tierDiscount > 0) {
                    $couponError = 'Kode promo ini tidak dapat digabungkan dengan diskon tier member.';
                } else {
                    $appliedPromotion = $promo;

                    if ($promo->type === Promotion::TYPE_PERCENTAGE) {
                        // Diskon persentase dari subtotal mobil
                        $calculated = (int) round(($carSubtotal * $promo->value) / 100);
                        if ($promo->max_discount !== null && $promo->max_discount > 0) {
                            $calculated = min($calculated, $promo->max_discount);
                        }
                    } else {
                        // Diskon nominal tetap
                        $calculated = (int) $promo->value;
                    }

                    // Pastikan tidak melebihi sisa subtotal mobil
                    $promoDiscount = min($calculated, $remainingSubtotal);
                    $remainingSubtotal = max(0, $remainingSubtotal - $promoDiscount);

                    $snapshot['promo'] = [
                        'code' => $promo->code,
                        'name' => $promo->name,
                        'type' => $promo->type,
                        'value' => $promo->value,
                        'amount' => $promoDiscount,
                    ];
                }
            } else {
                $couponError = $validation['message'];
            }
        }

        $totalDiscount = $tierDiscount + $promoDiscount;

        $snapshot['car_subtotal'] = $carSubtotal;
        $snapshot['total_discount'] = $totalDiscount;

        return [
            'tier_discount' => $tierDiscount,
            'promo_discount' => $promoDiscount,
            'total_discount' => $totalDiscount,
            'discount_snapshot' => $snapshot,
            'promotion' => $appliedPromotion,
            'coupon_error' => $couponError,
        ];
    }

    /**
     * Catat pemakaian promo ke tabel promotion_redemptions dan tingkatkan used_count.
     */
    public function recordRedemption(Booking $booking): ?PromotionRedemption
    {
        if (empty($booking->promo_code) || $booking->promo_discount <= 0) {
            return null;
        }

        $promo = Promotion::where('code', $booking->promo_code)->first();
        if (! $promo) {
            return null;
        }

        // Hindari pencatatan duplikat untuk booking yang sama
        $existing = PromotionRedemption::where('promotion_id', $promo->id)
            ->where('booking_id', $booking->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($promo, $booking) {
            $redemption = PromotionRedemption::create([
                'promotion_id' => $promo->id,
                'booking_id' => $booking->id,
                'user_id' => $booking->member_id ?: $booking->user_id,
                'customer_phone' => $booking->customer_phone,
                'discount_amount' => $booking->promo_discount,
            ]);

            $promo->increment('used_count');

            return $redemption;
        });
    }
}
