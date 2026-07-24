<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Exceptions\InvalidPaymentTransitionException;

class PaymentStatusService
{
    /**
     * @var array<string, list<PaymentStatus>>
     */
    private const TRANSITIONS = [
        'pending' => [
            PaymentStatus::AUTHORIZED,
            PaymentStatus::PAID,
            PaymentStatus::FAILED,
            PaymentStatus::CANCELLED,
            PaymentStatus::EXPIRED,
        ],
        'authorized' => [
            PaymentStatus::PAID,
            PaymentStatus::FAILED,
            PaymentStatus::CANCELLED,
            PaymentStatus::EXPIRED,
        ],
        'paid' => [
            PaymentStatus::REFUNDED,
            PaymentStatus::CHARGEBACK,
        ],
        'failed' => [
            PaymentStatus::PENDING,
        ],
        'cancelled' => [],
        'refunded' => [],
        'chargeback' => [],
        'expired' => [
            PaymentStatus::PENDING,
        ],
    ];

    public function canTransition(PaymentStatus $from, PaymentStatus $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return in_array($to, self::TRANSITIONS[$from->value] ?? [], true);
    }

    public function assertCanTransition(PaymentStatus $from, PaymentStatus $to): void
    {
        if (! $this->canTransition($from, $to)) {
            throw new InvalidPaymentTransitionException($from, $to);
        }
    }
}
