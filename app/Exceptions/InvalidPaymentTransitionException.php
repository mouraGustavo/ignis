<?php

namespace App\Exceptions;

use App\Enums\PaymentStatus;
use Exception;

class InvalidPaymentTransitionException extends Exception
{
    public function __construct(PaymentStatus $from, PaymentStatus $to)
    {
        parent::__construct("Transição de pagamento inválida: {$from->value} → {$to->value}");
    }
}
