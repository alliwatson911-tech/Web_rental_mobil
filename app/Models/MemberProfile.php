<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberProfile extends Model
{
    use HasFactory;

    public const KYC_UNVERIFIED = 'unverified';
    public const KYC_PENDING = 'pending';
    public const KYC_VERIFIED = 'verified';
    public const KYC_REJECTED = 'rejected';

    public const KYC_STATUSES = [
        self::KYC_UNVERIFIED => 'Belum Lengkap',
        self::KYC_PENDING => 'Menunggu Verifikasi',
        self::KYC_VERIFIED => 'Terverifikasi Sah',
        self::KYC_REJECTED => 'Ditolak / Perlu Perbaikan',
    ];

    protected $fillable = [
        'user_id',
        'membership_tier_id',
        'manual_tier_id',
        'manual_tier_notes',
        'manual_tier_updated_at',
        'manual_tier_updated_by',
        'total_completed_rentals',
        'total_spend',
        'loyalty_points',
        'is_active',
        'phone_verified_at',
        'address',
        'nik',
        'sim_number',
        'kyc_status',
        'kyc_notes',
        'kyc_verified_at',
        'kyc_verified_by',
    ];

    protected function casts(): array
    {
        return [
            'total_completed_rentals' => 'integer',
            'total_spend' => 'integer',
            'loyalty_points' => 'integer',
            'is_active' => 'boolean',
            'phone_verified_at' => 'datetime',
            'manual_tier_updated_at' => 'datetime',
            'kyc_verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function automaticTier(): BelongsTo
    {
        return $this->belongsTo(MembershipTier::class, 'membership_tier_id');
    }

    public function manualTier(): BelongsTo
    {
        return $this->belongsTo(MembershipTier::class, 'manual_tier_id');
    }

    public function manualTierUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manual_tier_updated_by');
    }

    public function kycVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kyc_verified_by');
    }

    /** Tier efektif yang berlaku bagi member (manual override diprioritaskan bila ada). */
    public function effectiveTier(): MembershipTier
    {
        return $this->manualTier ?: $this->automaticTier;
    }

    public function hasManualTier(): bool
    {
        return $this->manual_tier_id !== null;
    }

    public function discountPercentage(): int
    {
        return (int) $this->effectiveTier()->discount_percentage;
    }

    public function isKycVerified(): bool
    {
        return $this->kyc_status === self::KYC_VERIFIED;
    }

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function kycStatusLabel(): string
    {
        return self::KYC_STATUSES[$this->kyc_status] ?? ucfirst($this->kyc_status);
    }
}

