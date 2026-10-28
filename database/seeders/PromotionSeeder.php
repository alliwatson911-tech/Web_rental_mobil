<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $promotions = [
            [
                'name' => 'Voucher Potongan Rp 50.000',
                'code' => 'PADAIDI50K',
                'target_type' => Promotion::TARGET_COUPON,
                'type' => Promotion::TYPE_NOMINAL,
                'value' => 50000,
                'max_discount' => null,
                'min_order_amount' => 300000,
                'min_days' => 1,
                'usage_limit' => 100,
                'usage_limit_per_user' => 2,
                'is_combinable' => true,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(6),
                'description' => 'Voucher potongan langsung Rp 50.000 untuk transaksi sewa mobil minimal Rp 300.000.',
            ],
            [
                'name' => 'Diskon Promo Liburan 10%',
                'code' => 'HEMAT10',
                'target_type' => Promotion::TARGET_COUPON,
                'type' => Promotion::TYPE_PERCENTAGE,
                'value' => 10,
                'max_discount' => 100000,
                'min_order_amount' => 500000,
                'min_days' => 2,
                'usage_limit' => 50,
                'usage_limit_per_user' => 1,
                'is_combinable' => true,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
                'description' => 'Diskon 10% maksimal Rp 100.000 untuk durasi sewa minimal 2 hari.',
            ],
            [
                'name' => 'Diskon Pelanggan Baru 15%',
                'code' => 'NEWUSER',
                'target_type' => Promotion::TARGET_FIRST_ORDER,
                'type' => Promotion::TYPE_PERCENTAGE,
                'value' => 15,
                'max_discount' => 150000,
                'min_order_amount' => 0,
                'min_days' => 1,
                'usage_limit' => 200,
                'usage_limit_per_user' => 1,
                'is_combinable' => false,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(12),
                'description' => 'Diskon 15% khusus pelanggan yang baru pertama kali menyewa di Padaidi Rental.',
            ],
        ];

        foreach ($promotions as $promo) {
            Promotion::updateOrCreate(
                ['code' => $promo['code']],
                $promo
            );
        }
    }
}

