<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu verifikasi',
        self::STATUS_VERIFIED => 'Terverifikasi',
        self::STATUS_REJECTED => 'Ditolak',
    ];

    public const METHODS = [
        'transfer' => 'Transfer bank',
        'tunai' => 'Tunai',
    ];

    protected $fillable = [
        'booking_id', 'amount', 'method', 'sender_name', 'proof_path', 'status',
        'paid_at', 'verified_by', 'verified_at', 'notes',
    ];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'paid_at' => 'datetime', 'verified_at' => 'datetime'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function proofUrl(): ?string
    {
        return $this->proof_path ? Storage::disk('public')->url($this->proof_path) : null;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }
}
