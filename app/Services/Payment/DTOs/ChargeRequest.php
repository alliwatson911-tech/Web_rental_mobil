<?php

namespace App\Services\Payment\DTOs;

use App\Models\Booking;
use Carbon\CarbonInterface;

class ChargeRequest
{
    public function __construct(
        public Booking $booking,
        public string $referenceId,
        public int $amount,
        public string $paymentType, // full, dp, settlement
        public string $channel,     // qris, va_bca, va_bni, va_bri, va_mandiri, manual_transfer
        public string $customerName,
        public string $customerPhone,
        public ?string $customerEmail,
        public string $description,
        public CarbonInterface $expiresAt,
    ) {}
}

