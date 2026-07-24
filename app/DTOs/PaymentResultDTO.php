<?php

namespace App\DTOs;

use App\Enums\PaymentStatus;
use App\Models\Payment;

readonly class PaymentResultDTO
{
    public function __construct(
        public Payment $payment,
        public PaymentStatus $status,
        public ?string $message = null,
        public bool $retryable = false,
    ) {
    }

    public function isSuccess(): bool
    {
        return in_array($this->status, [PaymentStatus::PAID, PaymentStatus::AUTHORIZED], true);
    }
}
