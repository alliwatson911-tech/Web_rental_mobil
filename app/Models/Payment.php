<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    public const CHANNEL_QRIS = 'qris';
    public const CHANNEL_VA_BCA = 'va_bca';
    public const CHANNEL_VA_BNI = 'va_bni';
    public const CHANNEL_VA_BRI = 'va_bri';
    public const CHANNEL_VA_MANDIRI = 'va_mandiri';
    public const CHANNEL_VA_PERMATA = 'va_permata';
    public const CHANNEL_MANUAL_TRANSFER = 'manual_transfer';
    public const CHANNEL_CASH = 'cash';

    public const TYPE_FULL = 'full';
    public const TYPE_DP = 'dp';
    public const TYPE_SETTLEMENT = 'settlement';

    public const METHODS = [
        'transfer' => 'Transfer bank',
        'tunai' => 'Tunai',
        'gateway' => 'Payment Gateway',
    ];

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Pembayaran',
        self::STATUS_PAID => 'Berhasil (Lunas)',
        self::STATUS_VERIFIED => 'Terverifikasi Sah',
        self::STATUS_REJECTED => 'Ditolak',
        self::STATUS_FAILED => 'Gagal',
        self::STATUS_EXPIRED => 'Kedaluwarsa',
        self::STATUS_CANCELLED => 'Dibatalkan',
        self::STATUS_REFUNDED => 'Dikembalikan Penuh (Refund)',
        self::STATUS_PARTIALLY_REFUNDED => 'Dikembalikan Sebagian',
    ];

    public const CHANNELS = [
        self::CHANNEL_QRIS => 'QRIS (Semua Bank / E-Wallet)',
        self::CHANNEL_VA_BCA => 'BCA Virtual Account',
        self::CHANNEL_VA_BNI => 'BNI Virtual Account',
        self::CHANNEL_VA_BRI => 'BRI Virtual Account',
        self::CHANNEL_VA_MANDIRI => 'Mandiri Virtual Account',
        self::CHANNEL_VA_PERMATA => 'Permata Virtual Account',
        self::CHANNEL_MANUAL_TRANSFER => 'Transfer Bank Manual',
        self::CHANNEL_CASH => 'Tunai di Tempat',
    ];

    protected $fillable = [
        'booking_id',
        'reference_id',
        'external_id',
        'payment_type',
        'amount',
        'method',
        'channel',
        'gateway_name',
        'checkout_url',
        'qr_code_url',
        'va_number',
        'sender_name',
        'proof_path',
        'status',
        'paid_at',
        'expires_at',
        'verified_by',
        'verified_at',
        'notes',
        'raw_response',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'raw_response' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }

    public function proofUrl(): ?string
    {
        if (! $this->proof_path) {
            return null;
        }

        return route('bookings.payment.proof', ['booking' => $this->booking_id, 'payment' => $this->id]);
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, [
            self::STATUS_PAID,
            self::STATUS_VERIFIED,
            self::STATUS_PARTIALLY_REFUNDED,
        ], true);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        return $this->isPending() && $this->expires_at && $this->expires_at->isPast();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function channelLabel(): string
    {
        return self::CHANNELS[$this->channel] ?? ($this->channel ? strtoupper($this->channel) : 'Transfer Manual');
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? ($this->method ? ucfirst($this->method) : 'Transfer bank');
    }

    public function paymentTypeLabel(): string
    {
        return match ($this->payment_type) {
            self::TYPE_DP => 'Uang Muka (DP 30%)',
            self::TYPE_SETTLEMENT => 'Pelunasan Sisa',
            default => 'Pelunasan Penuh (100%)',
        };
    }

    public function refundableAmount(): int
    {
        if (! $this->isSuccessful()) {
            return 0;
        }

        $refunded = (int) $this->refunds()->where('status', PaymentRefund::STATUS_COMPLETED)->sum('amount');

        return max(0, $this->amount - $refunded);
    }

    public function badgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PAID, self::STATUS_VERIFIED => 'bg-emerald-100 text-emerald-800',
            self::STATUS_PENDING => 'bg-amber-100 text-amber-800',
            self::STATUS_REJECTED, self::STATUS_FAILED => 'bg-rose-100 text-rose-800',
            self::STATUS_EXPIRED, self::STATUS_CANCELLED => 'bg-slate-200 text-slate-700',
            self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED => 'bg-purple-100 text-purple-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }
}
