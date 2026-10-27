<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tahap lanjut (opsional) - belum dipakai di antarmuka. */
class CustomerDocument extends Model
{
    protected $fillable = ['booking_id', 'user_id', 'type', 'path'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
