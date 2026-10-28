<?php

namespace App\Services\Payment\DTOs;

class RefundResult
{
    public function __construct(
        public bool $success,
        public ?string $refundId = null,
        public int $amount = 0,
        public string $status = 'completed', // completed, pending, failed
        public array $rawResponse = [],
        public ?string $errorMessage = null,
    ) {}
}

