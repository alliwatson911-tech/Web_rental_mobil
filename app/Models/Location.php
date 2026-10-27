<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'requires_address', 'is_active'];

    protected function casts(): array
    {
        return ['requires_address' => 'boolean', 'is_active' => 'boolean'];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'pickup_location_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
