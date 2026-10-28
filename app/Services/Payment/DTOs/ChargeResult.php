<?php

namespace App\Services\Payment\DTOs;

class ChargeResult
{
    public function __construct(
        public bool $success,
        public string $referenceId,
        public ?string $externalId = null,
        public ?string $checkoutUrl = null,
        public ?string $qrCodeUrl = null,
        public ?string $vaNumber = null,
        public array $rawResponse = [],
        public ?string $errorMessage = null,
    ) {}
}

