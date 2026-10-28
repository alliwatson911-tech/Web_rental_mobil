<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_CUSTOMER = 'customer';

    protected $fillable = ['name', 'email', 'phone', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isMember(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function tier(): ?MembershipTier
    {
        return $this->memberProfile?->effectiveTier();
    }

    public function isKycVerified(): bool
    {
        return (bool) $this->memberProfile?->isKycVerified();
    }

    public function ensureMemberProfile(): MemberProfile
    {
        $profile = $this->memberProfile ?: $this->memberProfile()->first();
        if ($profile) {
            $this->setRelation('memberProfile', $profile);
            return $profile;
        }

        $defaultTier = MembershipTier::defaultTier();
        if (! $defaultTier) {
            $defaultTier = MembershipTier::create([
                'name' => 'Reguler',
                'slug' => MembershipTier::SLUG_REGULER,
                'min_completed_rentals' => 0,
                'discount_percentage' => 0,
                'badge_color' => 'slate',
                'is_active' => true,
            ]);
        }

        $profile = $this->memberProfile()->create([
            'membership_tier_id' => $defaultTier->id,
            'is_active' => true,
            'kyc_status' => MemberProfile::KYC_UNVERIFIED,
        ]);

        $this->setRelation('memberProfile', $profile);

        return $profile;
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function memberBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'member_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function loyaltyLedgers(): HasMany
    {
        return $this->hasMany(LoyaltyLedger::class);
    }
}
