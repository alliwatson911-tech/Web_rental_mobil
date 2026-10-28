<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyLedger extends Model
{
    public $timestamps = false;

    public const TYPE_EARN = 'earn';
    public const TYPE_REDEEM = 'redeem';
    public const TYPE_ADJUST = 'adjust';

    protected $fillable = [
        'user_id',
        'booking_id',
        'type',
        'points',
        'balance_after',
        'description',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'balance_after' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

