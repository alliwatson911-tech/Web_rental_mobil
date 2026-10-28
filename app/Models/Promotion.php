<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_NOMINAL = 'nominal';

    public const TARGET_TIER = 'tier';
    public const TARGET_COUPON = 'coupon';
    public const TARGET_FIRST_ORDER = 'first_order';
    public const TARGET_PERIOD = 'period';

    public const TARGET_TYPES = [
        self::TARGET_COUPON => 'Kode Promo (Kupon)',
        self::TARGET_FIRST_ORDER => 'Pelanggan Baru',
        self::TARGET_PERIOD => 'Periode / Musiman',
        self::TARGET_TIER => 'Diskon Tier Member',
    ];

    protected $fillable = [
        'name',
        'code',
        'target_type',
        'type',
        'value',
        'max_discount',
        'min_order_amount',
        'min_days',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'is_combinable',
        'is_active',
        'category_id',
        'car_id',
        'location_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'max_discount' => 'integer',
            'min_order_amount' => 'integer',
            'min_days' => 'integer',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'used_count' => 'integer',
            'is_combinable' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromotionRedemption::class);
    }

    public function scopeActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function isExpired(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }

    public function isStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    public function isQuotaAvailable(): bool
    {
        if ($this->usage_limit === null) {
            return true;
        }

        return $this->redemptions()->count() < $this->usage_limit;
    }

    public function isUserQuotaAvailable(?User $user, ?string $phone = null): bool
    {
        if ($this->usage_limit_per_user === null) {
            return true;
        }

        $query = $this->redemptions();

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($phone) {
            $query->where('customer_phone', $phone);
        } else {
            return true;
        }

        return $query->count() < $this->usage_limit_per_user;
    }

    /**
     * Hitung nilai nominal potongan atas subtotal sewa mobil.
     */
    public function calculateDiscount(int $carSubtotal): int
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            $discount = (int) round(($carSubtotal * $this->value) / 100);
            if ($this->max_discount !== null && $this->max_discount > 0) {
                $discount = min($discount, $this->max_discount);
            }
        } else {
            $discount = $this->value;
        }

        return max(0, min($discount, $carSubtotal));
    }

    public function targetTypeLabel(): string
    {
        return self::TARGET_TYPES[$this->target_type] ?? ucfirst($this->target_type);
    }

    public function typeLabel(): string
    {
        return $this->type === self::TYPE_PERCENTAGE ? 'Persentase (%)' : 'Nominal Tetap (Rp)';
    }

    public function formattedValue(): string
    {
        return $this->type === self::TYPE_PERCENTAGE ? "{$this->value}%" : rupiah($this->value);
    }
}

