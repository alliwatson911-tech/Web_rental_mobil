<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDocument extends Model
{
    public const TYPE_KTP = 'ktp';
    public const TYPE_SIM = 'sim';

    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';

    public const TYPES = [
        self::TYPE_KTP => 'e-KTP (Identitas)',
        self::TYPE_SIM => 'SIM A (Izin Mengemudi)',
    ];

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Verifikasi',
        self::STATUS_VERIFIED => 'Terverifikasi Valid',
        self::STATUS_REJECTED => 'Ditolak / Tidak Jelas',
    ];

    protected $fillable = [
        'booking_id',
        'user_id',
        'type',
        'path',
        'status',
        'notes',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? strtoupper($this->type);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function documentUrl(): ?string
    {
        if (! $this->path) {
            return null;
        }

        return route('bookings.document.show', ['booking' => $this->booking_id, 'document' => $this->id]);
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }
}
