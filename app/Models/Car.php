<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Car extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_MAINTENANCE = 'maintenance';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_AVAILABLE => 'Tersedia',
        self::STATUS_MAINTENANCE => 'Perawatan',
        self::STATUS_INACTIVE => 'Tidak aktif',
    ];

    public const TRANSMISSIONS = [
        'manual' => 'Manual',
        'automatic' => 'Otomatis',
    ];

    protected $fillable = [
        'category_id', 'name', 'slug', 'brand', 'year', 'plate_number', 'transmission',
        'seats', 'fuel', 'daily_price', 'status', 'description',
    ];

    protected function casts(): array
    {
        return ['daily_price' => 'integer', 'seats' => 'integer', 'year' => 'integer'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(CarPhoto::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
    }

    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(CarPhoto::class)->ofMany(['is_primary' => 'max', 'id' => 'min']);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(CarBlock::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function photoUrl(): ?string
    {
        $photo = $this->relationLoaded('primaryPhoto') ? $this->primaryPhoto : $this->primaryPhoto()->first();

        return $photo ? Storage::disk('public')->url($photo->path) : null;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function transmissionLabel(): string
    {
        return self::TRANSMISSIONS[$this->transmission] ?? $this->transmission;
    }
}
