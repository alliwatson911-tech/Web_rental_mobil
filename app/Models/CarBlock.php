<?php

namespace App\Models;

use App\Casts\DateOnly;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jadwal mobil tidak tersedia (mis. servis). start_date s.d. end_date INKLUSIF.
 */
class CarBlock extends Model
{
    use HasFactory;

    protected $fillable = ['car_id', 'start_date', 'end_date', 'reason', 'notes'];

    protected function casts(): array
    {
        return ['start_date' => DateOnly::class, 'end_date' => DateOnly::class];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
