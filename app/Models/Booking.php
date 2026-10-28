<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'user_id', 'member_id', 'car_id', 'pickup_location_id', 'delivery_address',
        'customer_name', 'customer_phone', 'customer_email', 'customer_address',
        'pickup_date', 'return_date', 'days',
        'daily_price', 'base_total', 'addons_total', 'discount', 'total',
        'promo_code', 'tier_discount', 'promo_discount', 'discount_snapshot',
        'status', 'notes', 'admin_notes', 'cancel_reason',
        'confirmed_at', 'picked_up_at', 'completed_at', 'cancelled_at',
        'start_odometer', 'end_odometer', 'fuel_out', 'fuel_in',
        'handover_notes', 'return_notes', 'driver_name', 'driver_phone',
        'overtime_hours', 'overtime_fee', 'extra_fee', 'extra_fee_notes',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'member_id' => 'integer',
            'pickup_date' => DateOnly::class,
            'return_date' => DateOnly::class,
            'status' => BookingStatus::class,
            'days' => 'integer',
            'daily_price' => 'integer',
            'base_total' => 'integer',
            'addons_total' => 'integer',
            'discount' => 'integer',
            'tier_discount' => 'integer',
            'promo_discount' => 'integer',
            'discount_snapshot' => 'array',
            'total' => 'integer',
            'start_odometer' => 'integer',
            'end_odometer' => 'integer',
            'overtime_hours' => 'integer',
            'overtime_fee' => 'integer',
            'extra_fee' => 'integer',
            'confirmed_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public static function generateCode(): string
    {
        do {
            $code = 'PDD-'.strtoupper(Str::random(6));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    public function addons(): HasMany
    {
        return $this->hasMany(BookingAddon::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function ktpDocument(): ?CustomerDocument
    {
        return $this->documents->firstWhere('type', CustomerDocument::TYPE_KTP);
    }

    public function simDocument(): ?CustomerDocument
    {
        return $this->documents->firstWhere('type', CustomerDocument::TYPE_SIM);
    }

    public function isKycComplete(): bool
    {
        return (bool) ($this->ktpDocument() && $this->simDocument());
    }

    public function hasDriver(): bool
    {
        return $this->addons->contains(function ($line) {
            return ($line->addon && $line->addon->code === 'driver')
                || Str::contains(strtolower($line->name), ['sopir', 'driver']);
        });
    }

    public function distanceDriven(): ?int
    {
        if ($this->start_odometer !== null && $this->end_odometer !== null) {
            return max(0, $this->end_odometer - $this->start_odometer);
        }

        return null;
    }

    public function grandTotal(): int
    {
        return $this->total + (int) $this->overtime_fee + (int) $this->extra_fee;
    }

    public function paidAmount(): int
    {
        $gross = (int) $this->payments
            ->whereIn('status', [Payment::STATUS_VERIFIED, Payment::STATUS_PAID, Payment::STATUS_PARTIALLY_REFUNDED])
            ->sum('amount');

        $refunds = 0;
        foreach ($this->payments as $payment) {
            $refunds += (int) $payment->refunds()->where('status', PaymentRefund::STATUS_COMPLETED)->sum('amount');
        }

        return max(0, $gross - $refunds);
    }

    public function remainingAmount(): int
    {
        return max(0, $this->grandTotal() - $this->paidAmount());
    }

    public function paymentStatusLabel(): string
    {
        if ($this->paidAmount() === 0) {
            return 'Belum Bayar';
        }

        if ($this->isPaidOff()) {
            return 'Lunas';
        }

        return 'DP Terbayar (Sebagian)';
    }

    public function paymentBadgeClass(): string
    {
        if ($this->paidAmount() === 0) {
            return 'bg-amber-100 text-amber-800';
        }

        if ($this->isPaidOff()) {
            return 'bg-emerald-100 text-emerald-800';
        }

        return 'bg-sky-100 text-sky-800';
    }

    public function scopeOverlapping($query, string $pickup, string $return)
    {
        return $query->where('pickup_date', '<', $return)->where('return_date', '>', $pickup);
    }

    public function scopeBlocking($query)
    {
        return $query->whereIn('status', BookingStatus::blocking());
    }

    public function isPaidOff(): bool
    {
        return $this->remainingAmount() === 0;
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromotionRedemption::class);
    }
}
