<?php

namespace App\Exceptions;

use Exception;

class PaymentDeclinedException extends Exception
{
    public function __construct(
        string $message,
        public readonly bool $retryable = false,
    ) {
        parent::__construct($message);
    }
}
