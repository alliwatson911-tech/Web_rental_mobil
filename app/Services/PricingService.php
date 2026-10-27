<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\Car;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Menghitung harga sewa. SELALU dipakai di server; total dari browser tidak dipercaya.
 *
 * Total = harga per hari x jumlah hari + total tambahan - diskon
 */
class PricingService
{
    /** Jumlah hari = tanggal kembali - tanggal ambil, minimal 1 hari. */
    public function days(CarbonInterface|string $pickup, CarbonInterface|string $return): int
    {
        $p = CarbonImmutable::parse($pickup)->startOfDay();
        $r = CarbonImmutable::parse($return)->startOfDay();

        return max(1, (int) $p->diffInDays($r, false));
    }

    /**
     * @param  Collection<int, Addon>|iterable<Addon>  $addons
     * @return array{days:int,daily_price:int,base_total:int,addons:array<int,array>,addons_total:int,discount:int,total:int}
     */
    public function quote(Car $car, CarbonInterface|string $pickup, CarbonInterface|string $return, iterable $addons = [], int $discount = 0): array
    {
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
        $discount = max(0, min($discount, $baseTotal + $addonsTotal));

        return [
            'days' => $days,
            'daily_price' => $dailyPrice,
            'base_total' => $baseTotal,
            'addons' => $lines,
            'addons_total' => $addonsTotal,
            'discount' => $discount,
            'total' => $baseTotal + $addonsTotal - $discount,
        ];
    }
}
