<?php

namespace App\Services;

use App\Exports\ParticipantesExport;
use App\Mail\LimiteVagasAtingidoMail;
use App\Models\Evento;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class VagasLimiteService
{
    /**
     * Ao atingir o limite de vagas confirmadas: bloqueia novas inscricoes
     * (settings.registration_open = false), gera o .xlsx com nome + telefone de todos
     * os participantes e envia por e-mail ao coordenador. Conforme especificacao
     * funcional, secao "Bloqueio e Fechamento".
     */
    public function verificarLimite(Evento $evento): void
    {
        if (! $evento->atingiuLimiteVagas()) {
            return;
        }

        if (Setting::get('registration_open') === 'false') {
            return; // ja processado anteriormente, evita reenvio duplicado
        }

        Setting::set('registration_open', 'false');

        $coordinatorEmail = Setting::get('coordinator_email');

        if (! $coordinatorEmail) {
            return; // nada configurado; evita erro de envio sem destinatario
        }

        $arquivo = Excel::raw(new ParticipantesExport($evento), \Maatwebsite\Excel\Excel::XLSX);

        Mail::to($coordinatorEmail)->queue(
            (new LimiteVagasAtingidoMail($evento))->attachData(
                $arquivo,
                'participantes-' . $evento->id . '.xlsx',
                ['mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            )
        );
    }
}
