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
        'code', 'user_id', 'car_id', 'pickup_location_id', 'delivery_address',
        'customer_name', 'customer_phone', 'customer_email', 'customer_address',
        'pickup_date', 'return_date', 'days',
        'daily_price', 'base_total', 'addons_total', 'discount', 'total',
        'status', 'notes', 'admin_notes', 'cancel_reason',
        'confirmed_at', 'picked_up_at', 'completed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => DateOnly::class,
            'return_date' => DateOnly::class,
            'status' => BookingStatus::class,
            'days' => 'integer',
            'daily_price' => 'integer',
            'base_total' => 'integer',
            'addons_total' => 'integer',
            'discount' => 'integer',
            'total' => 'integer',
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

    /** Rentang tanggal beririsan dengan [pickup, return). */
    public function scopeOverlapping($query, string $pickup, string $return)
    {
        return $query->where('pickup_date', '<', $return)->where('return_date', '>', $pickup);
    }

    public function scopeBlocking($query)
    {
        return $query->whereIn('status', BookingStatus::blocking());
    }

    public function paidAmount(): int
    {
        return (int) $this->payments->where('status', Payment::STATUS_VERIFIED)->sum('amount');
    }

    public function remainingAmount(): int
    {
        return max(0, $this->total - $this->paidAmount());
    }

    public function isPaidOff(): bool
    {
        return $this->remainingAmount() === 0;
    }
}
