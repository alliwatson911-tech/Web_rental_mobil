<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\LoyaltyLedger;
use App\Models\MemberProfile;
use App\Models\MembershipTier;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    /** Sinkronisasi tier otomatis berdasarkan riwayat sewa selesai. */
    public function syncTier(User $user): MemberProfile
    {
        return DB::transaction(function () use ($user) {
            $profile = $user->ensureMemberProfile();

            // Hitung total sewa selesai yang valid untuk member ini
            $completedBookings = Booking::query()
                ->where(function ($q) use ($user) {
                    $q->where('member_id', $user->id)
                      ->orWhere(function ($q2) use ($user) {
                          $q2->where('user_id', $user->id)->whereNull('member_id');
                      });
                })
                ->where('status', BookingStatus::Completed);

            $completedCount = (int) $completedBookings->count();
            $totalSpend = (int) $completedBookings->sum('total');

            $profile->total_completed_rentals = $completedCount;
            $profile->total_spend = $totalSpend;

            // Jika manual tier TIDAK diset, hitung tier otomatis
            if (! $profile->manual_tier_id) {
                $targetTier = MembershipTier::active()
                    ->where('min_completed_rentals', '<=', $completedCount)
                    ->orderByDesc('min_completed_rentals')
                    ->first() ?: MembershipTier::defaultTier();

                if ($targetTier) {
                    $profile->membership_tier_id = $targetTier->id;
                }
            } else {
                // Jika manual tier diset, membership_tier_id tetap mengikuti manual_tier_id
                $profile->membership_tier_id = $profile->manual_tier_id;
            }

            $profile->save();

            return $profile;
        });
    }

    /** Mencatat penyelesaian sewa: akumulasi sewa, hitung poin loyalitas, dan update tier. */
    public function recordCompletedRental(Booking $booking): ?MemberProfile
    {
        $memberId = $booking->member_id ?: $booking->user_id;
        if (! $memberId) {
            return null;
        }

        $user = User::find($memberId);
        if (! $user || ! $user->isMember()) {
            return null;
        }

        return DB::transaction(function () use ($user, $booking) {
            $profile = $user->ensureMemberProfile();

            // Aturan poin loyalitas: 1 poin per Rp 100.000 sewa (min 1 poin jika total > 0)
            $earnPoints = max(1, (int) floor($booking->total / 100000));
            $newBalance = $profile->loyalty_points + $earnPoints;

            LoyaltyLedger::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'type' => LoyaltyLedger::TYPE_EARN,
                'points' => $earnPoints,
                'balance_after' => $newBalance,
                'description' => "Poin sewa selesai: {$booking->code} ({$booking->car->name})",
                'created_at' => now(),
            ]);

            $profile->loyalty_points = $newBalance;
            $profile->save();

            return $this->syncTier($user);
        });
    }

    /** Admin menetapkan tier secara manual dengan catatan alasan. */
    public function setManualTier(User $user, MembershipTier $tier, string $notes, User $admin): MemberProfile
    {
        $profile = $user->ensureMemberProfile();
        $profile->manual_tier_id = $tier->id;
        $profile->membership_tier_id = $tier->id;
        $profile->manual_tier_notes = $notes;
        $profile->manual_tier_updated_at = now();
        $profile->manual_tier_updated_by = $admin->id;
        $profile->save();

        return $profile;
    }

    /** Admin mengembalikan tier ke perhitungan otomatis. */
    public function resetManualTier(User $user): MemberProfile
    {
        $profile = $user->ensureMemberProfile();
        $profile->manual_tier_id = null;
        $profile->manual_tier_notes = 'Direset kembali ke perhitungan otomatis oleh admin.';
        $profile->manual_tier_updated_at = now();
        $profile->save();

        return $this->syncTier($user);
    }

    /** Verifikasi status KYC member oleh admin. */
    public function updateKycStatus(User $user, string $status, ?string $notes, User $admin): MemberProfile
    {
        $profile = $user->ensureMemberProfile();
        $profile->kyc_status = $status;
        $profile->kyc_notes = $notes;
        $profile->kyc_verified_at = now();
        $profile->kyc_verified_by = $admin->id;
        $profile->save();

        // Update dokumen customer_documents terkait user ini
        $docStatus = match ($status) {
            MemberProfile::KYC_VERIFIED => \App\Models\CustomerDocument::STATUS_VERIFIED,
            MemberProfile::KYC_REJECTED => \App\Models\CustomerDocument::STATUS_REJECTED,
            default => \App\Models\CustomerDocument::STATUS_PENDING,
        };

        $user->documents()->update([
            'status' => $docStatus,
            'notes' => $notes,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        return $profile;
    }
}

