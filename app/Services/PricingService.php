<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\Car;
use App\Models\Location;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Menghitung harga sewa. SELALU dipakai di server; total dari browser tidak dipercaya.
 *
 * Total = harga per hari x jumlah hari + total tambahan - diskon (tier + promo)
 */
class PricingService
{
    public function __construct(
        protected ?DiscountService $discounts = null
    ) {
        $this->discounts = $discounts ?? app(DiscountService::class);
    }

    /** Jumlah hari = tanggal kembali - tanggal ambil, minimal 1 hari. */
    public function days(CarbonInterface|string $pickup, CarbonInterface|string $return): int
    {
        $p = CarbonImmutable::parse($pickup)->startOfDay();
        $r = CarbonImmutable::parse($return)->startOfDay();

        return max(1, (int) $p->diffInDays($r, false));
    }

    /**
     * Menghitung rincian sewa lengkap dengan integrasi mesin diskon tier & kupon promo.
     *
     * @param  Collection<int, Addon>|iterable<Addon>  $addons
     * @return array{
     *     days: int,
     *     daily_price: int,
     *     base_total: int,
     *     addons: array<int, array>,
     *     addons_total: int,
     *     tier_discount: int,
     *     promo_discount: int,
     *     discount: int,
     *     discount_snapshot: array,
     *     coupon_error: ?string,
     *     promo_code: ?string,
     *     total: int,
     *     dp_amount: int,
     *     settlement_amount: int
     * }
     */
    public function quote(
        Car $car,
        CarbonInterface|string $pickup,
        CarbonInterface|string $return,
        iterable $addons = [],
        int $discount = 0,
        ?User $user = null,
        ?string $promoCode = null,
        ?string $phone = null,
        ?Location $location = null
    ): array {
        $days = $this->days($pickup, $return);
        $dailyPrice = (int) $car->daily_price;
        $baseTotal = $dailyPrice * $days;

        $lines = [];
        foreach ($addons as $addon) {
            $quantity = $addon->charge_type === Addon::PER_DAY ? $days : 1;
            $lines[] = [
                'addon_id' => $addon->id,
                'name' => $addon->name,
                'charge_type' => $addon->charge_type,
                'unit_price' => (int) $addon->price,
                'quantity' => $quantity,
                'subtotal' => (int) $addon->price * $quantity,
            ];
        }

        $addonsTotal = array_sum(array_column($lines, 'subtotal'));

        $tierDiscount = 0;
        $promoDiscount = 0;
        $snapshot = [];
        $couponError = null;
        $cleanPromoCode = ! empty($promoCode) ? strtoupper(trim($promoCode)) : null;

        if ($user !== null || ! empty($cleanPromoCode)) {
            $calc = $this->discounts->calculate($car, $days, $baseTotal, $user, $cleanPromoCode, $phone, $location);
            $tierDiscount = $calc['tier_discount'];
            $promoDiscount = $calc['promo_discount'];
            $discount = $calc['total_discount'];
            $snapshot = $calc['discount_snapshot'];
            $couponError = $calc['coupon_error'];
        } else {
            // Diskon manual (misal dari testing atau parameter langsung)
            $discount = max(0, min($discount, $baseTotal));
            $promoDiscount = $discount;
        }

        // Aturan: total diskon hanya memotong subtotal mobil (tidak boleh melebihi base_total)
        $discount = max(0, min($discount, $baseTotal));
        $total = max(0, $baseTotal + $addonsTotal - $discount);

        // Uang Muka (DP 30%) dan sisa pelunasan (70%)
        $dpAmount = (int) round($total * 0.30);
        $settlementAmount = $total - $dpAmount;

        return [
            'days' => $days,
            'daily_price' => $dailyPrice,
            'base_total' => $baseTotal,
            'addons' => $lines,
            'addons_total' => $addonsTotal,
            'tier_discount' => $tierDiscount,
            'promo_discount' => $promoDiscount,
            'discount' => $discount,
            'discount_snapshot' => $snapshot,
            'coupon_error' => $couponError,
            'promo_code' => $cleanPromoCode,
            'total' => $total,
            'dp_amount' => $dpAmount,
            'settlement_amount' => $settlementAmount,
        ];
    }
}
