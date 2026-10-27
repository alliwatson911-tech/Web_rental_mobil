<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingAddon extends Model
{
    protected $fillable = ['booking_id', 'addon_id', 'name', 'charge_type', 'unit_price', 'quantity', 'subtotal'];

    protected function casts(): array
    {
        return ['unit_price' => 'integer', 'quantity' => 'integer', 'subtotal' => 'integer'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }
}
