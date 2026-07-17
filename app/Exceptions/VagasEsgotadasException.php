<?php

namespace App\Exceptions;

use Exception;

class VagasEsgotadasException extends Exception
{
    protected $message = 'As inscrições estão encerradas: o limite de vagas foi atingido.';
}
