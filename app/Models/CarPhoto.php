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

    public function webpUrl(): ?string
    {
        if (empty($this->path)) {
            return null;
        }

        $extension = strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
        if ($extension === 'webp') {
            return $this->url();
        }

        $dir = pathinfo($this->path, PATHINFO_DIRNAME);
        $filename = pathinfo($this->path, PATHINFO_FILENAME);
        $webpPath = ($dir === '.' ? '' : $dir . '/') . $filename . '.webp';

        if (Storage::disk('public')->exists($webpPath)) {
            return Storage::disk('public')->url($webpPath);
        }

        return null;
    }
}
