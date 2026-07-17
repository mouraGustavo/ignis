<?php

namespace App\Mail;

use App\Models\EventoInscrito;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InscricaoRecebidaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly EventoInscrito $eventoInscrito)
    {
    }

    public function build(): self
    {
        return $this
            ->subject('Inscrição recebida - ' . $this->eventoInscrito->evento->titulo)
            ->view('emails.inscricao-recebida');
    }
}
