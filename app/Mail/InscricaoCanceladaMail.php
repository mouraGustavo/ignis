<?php

namespace App\Mail;

use App\Models\EventoInscrito;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InscricaoCanceladaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly EventoInscrito $eventoInscrito)
    {
    }

    public function build(): self
    {
        return $this
            ->subject('Inscrição cancelada - ' . $this->eventoInscrito->evento->titulo)
            ->markdown('emails.inscricao-cancelada');
    }
}
