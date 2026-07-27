<?php

namespace App\Mail;

use App\Models\Evento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LimiteVagasAtingidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Evento $evento)
    {
    }

    public function build(): self
    {
        return $this
            ->subject('Limite de vagas atingido - ' . $this->evento->titulo)
            ->markdown('emails.limite-vagas-atingido');
    }
}
