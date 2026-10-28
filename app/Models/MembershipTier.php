<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipTier extends Model
{
    use HasFactory;

    public const SLUG_REGULER = 'reguler';
    public const SLUG_SILVER = 'silver';
    public const SLUG_GOLD = 'gold';

    public const TIER_REGULER = self::SLUG_REGULER;
    public const TIER_SILVER = self::SLUG_SILVER;
    public const TIER_GOLD = self::SLUG_GOLD;

    protected $fillable = [
        'name',
        'slug',
        'min_completed_rentals',
        'discount_percentage',
        'description',
        'badge_color',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_completed_rentals' => 'integer',
            'discount_percentage' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function memberProfiles(): HasMany
    {
        return $this->hasMany(MemberProfile::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function defaultTier(): ?self
    {
        return static::where('slug', self::SLUG_REGULER)->first()
            ?? static::orderBy('min_completed_rentals')->first();
    }
}
