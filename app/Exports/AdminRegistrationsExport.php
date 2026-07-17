<?php

namespace App\Exports;

use App\Models\EventoInscrito;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminRegistrationsExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return EventoInscrito::query()
            ->with(['inscrito.contato', 'inscrito.tamanhoCamisa', 'status'])
            ->get()
            ->map(fn (EventoInscrito $ei) => [
                'numero_inscricao' => $ei->numero_inscricao,
                'nome' => $ei->inscrito->nome,
                'idade' => $ei->inscrito->idade,
                'email' => $ei->inscrito->contato->email ?? '',
                'telefone' => $ei->inscrito->contato->telefone ?? '',
                'tamanho_camisa' => $ei->inscrito->tamanhoCamisa->tamanho ?? '',
                'status' => $ei->status->nome,
                'metodo_pagamento' => $ei->metodo_pagamento,
                'pagamento_confirmado' => $ei->pagamento_confirmado ? 'sim' : 'não',
            ]);
    }

    public function headings(): array
    {
        return [
            'Nº Inscrição', 'Nome', 'Idade', 'E-mail', 'Telefone',
            'Tamanho Camiseta', 'Status', 'Método de Pagamento', 'Pagamento Confirmado',
        ];
    }
}
