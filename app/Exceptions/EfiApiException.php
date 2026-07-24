<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class EfiApiException extends Exception
{
    public function __construct(
        string $message = 'Erro na API Efí',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?string $error = null,
        public readonly mixed $errorDescription = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function isTemporary(): bool
    {
        return in_array($this->code, [408, 500, 502, 503, 504], true);
    }
}
