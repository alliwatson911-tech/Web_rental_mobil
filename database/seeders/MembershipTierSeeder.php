<?php

namespace Database\Seeders;

use App\Models\MembershipTier;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MembershipTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Reguler',
                'slug' => MembershipTier::TIER_REGULER,
                'min_completed_rentals' => 0,
                'discount_percentage' => 0,
                'description' => 'Tier awal pelanggan terdaftar. Dapatkan poin loyalitas pada setiap transaksi.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Silver',
                'slug' => MembershipTier::TIER_SILVER,
                'min_completed_rentals' => 3,
                'discount_percentage' => 5,
                'description' => 'Diskon 5% sewa mobil untuk pelanggan setia dengan minimal 3 sewa selesai.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Gold',
                'slug' => MembershipTier::TIER_GOLD,
                'min_completed_rentals' => 10,
                'discount_percentage' => 10,
                'description' => 'Diskon 10% sewa mobil untuk pelanggan prioritas dengan minimal 10 sewa selesai.',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tierData) {
            MembershipTier::updateOrCreate(
                ['slug' => $tierData['slug']],
                $tierData
            );
        }

        // Akun member contoh untuk pengetesan
        $regulerTier = MembershipTier::where('slug', MembershipTier::TIER_REGULER)->first();

        $member = User::updateOrCreate(
            ['email' => 'member@padaidi.test'],
            [
                'name' => 'Budi Santoso (Member Contoh)',
                'phone' => '081234567890',
                'role' => User::ROLE_CUSTOMER,
                'password' => Hash::make('padaidi-member-123'),
                'email_verified_at' => now(),
            ]
        );

        $profile = $member->ensureMemberProfile();
        $profile->update([
            'membership_tier_id' => $regulerTier?->id,
            'phone_verified_at' => now(),
            'kyc_status' => MemberProfile::KYC_UNVERIFIED,
            'is_active' => true,
        ]);
    }
}
