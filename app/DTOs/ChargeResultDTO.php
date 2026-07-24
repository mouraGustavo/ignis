<?php

namespace App\DTOs;

use App\Enums\PaymentStatus;

readonly class ChargeResultDTO
{
    public function __construct(
        public ?string $chargeId,
        public PaymentStatus $status,
        public ?string $refusalReason = null,
        public bool $retryable = false,
        public ?array $rawResponse = null,
    ) {
    }

    public function isApproved(): bool
    {
        return $this->status === PaymentStatus::PAID;
    }

    public function isWaiting(): bool
    {
        return $this->status === PaymentStatus::AUTHORIZED;
    }

    public function isDeclined(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }
}
