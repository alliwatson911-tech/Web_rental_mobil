<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CarPhoto extends Model
{
    protected $fillable = ['car_id', 'path', 'is_primary', 'sort_order'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
