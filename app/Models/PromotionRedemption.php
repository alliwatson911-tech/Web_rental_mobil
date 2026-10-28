<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'booking_id',
        'user_id',
        'customer_phone',
        'discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'promotion_id' => 'integer',
            'booking_id' => 'integer',
            'user_id' => 'integer',
            'discount_amount' => 'integer',
        ];
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

