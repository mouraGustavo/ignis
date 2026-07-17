<?php

namespace App\Exports;

use App\Models\Evento;
use App\Models\StatusInscricao;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ParticipantesExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Evento $evento)
    {
    }

    public function collection(): Collection
    {
        return $this->evento->eventoInscritos()
            ->whereHas('status', fn ($q) => $q->where('nome', StatusInscricao::CONFIRMADA))
            ->with('inscrito.contato')
            ->get()
            ->map(fn ($eventoInscrito) => [
                'nome' => $eventoInscrito->inscrito->nome,
                'telefone' => $eventoInscrito->inscrito->contato->telefone ?? '',
            ]);
    }

    public function headings(): array
    {
        return ['Nome', 'Telefone/WhatsApp'];
    }
}
