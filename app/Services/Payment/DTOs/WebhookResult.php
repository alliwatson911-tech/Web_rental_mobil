<?php

namespace App\Services\Payment\DTOs;

use DateTimeInterface;

class WebhookResult
{
    public function __construct(
        public bool $valid,
        public ?string $eventId = null,
        public ?string $eventType = null,
        public ?string $referenceId = null,
        public ?string $externalId = null,
        public ?string $status = null, // paid, failed, expired
        public ?int $paidAmount = null,
        public ?DateTimeInterface $paidAt = null,
        public array $rawPayload = [],
        public ?string $errorMessage = null,
    ) {}
}

