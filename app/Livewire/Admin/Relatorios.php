<?php

namespace App\Livewire\Admin;

use App\Livewire\Inscricao;
use App\Models\EventoInscrito;
use App\Models\Setting;
use App\Models\StatusInscricao;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Relatorios extends Component
{
    private const AGE_GROUPS = [
        ['range' => '13-15', 'min' => 13, 'max' => 15],
        ['range' => '16-18', 'min' => 16, 'max' => 18],
        ['range' => '19-25', 'min' => 19, 'max' => 25],
        ['range' => '26+', 'min' => 26, 'max' => 999],
    ];

    public function render()
    {
        $regs = EventoInscrito::query()->with(['inscrito.tamanhoCamisa', 'status'])->get();
        $settings = Setting::allAsArray();

        $maxSpots = (int) ($settings['max_spots'] ?? 150);
        $value = (float) ($settings['registration_value'] ?? 150);

        $confirmed = $regs->filter(fn ($r) => $r->status->nome === StatusInscricao::CONFIRMADA);
        $cancelled = $regs->filter(fn ($r) => in_array($r->status->nome, [StatusInscricao::CANCELADA, StatusInscricao::RECUSADA], true));
        $pending = $regs->filter(fn ($r) => ! in_array($r->status->nome, [StatusInscricao::CONFIRMADA, StatusInscricao::CANCELADA, StatusInscricao::RECUSADA], true));

        $occupancyRate = $maxSpots > 0 ? (int) round(($confirmed->count() / $maxSpots) * 100) : 0;

        $statusData = collect([
            ['name' => 'Confirmados', 'value' => $confirmed->count(), 'color' => '#22c55e'],
            ['name' => 'Pendentes', 'value' => $pending->count(), 'color' => '#f59e0b'],
            ['name' => 'Cancelados', 'value' => $cancelled->count(), 'color' => '#ef4444'],
        ])->filter(fn ($d) => $d['value'] > 0)->values();

        $ageData = collect(self::AGE_GROUPS)->map(function ($g) use ($regs, $confirmed) {
            $inRange = fn ($r) => $r->inscrito->idade !== null && $r->inscrito->idade >= $g['min'] && $r->inscrito->idade <= $g['max'];

            return [
                'name' => $g['range'],
                'Total' => $regs->filter($inRange)->count(),
                'Confirmados' => $confirmed->filter($inRange)->count(),
            ];
        })->values();

        $pixConfirmed = $confirmed->where('metodo_pagamento', 'pix')->count();
        $cardConfirmed = $confirmed->where('metodo_pagamento', 'credit_card')->count();

        $financialData = collect([
            ['name' => 'PIX', 'value' => $pixConfirmed, 'color' => '#ffcc00'],
            ['name' => 'Cartão', 'value' => $cardConfirmed, 'color' => '#3b82f6'],
            ['name' => 'Pendente', 'value' => $pending->count(), 'color' => '#f59e0b'],
        ])->filter(fn ($d) => $d['value'] > 0)->values();

        $shirtData = collect(Inscricao::SHIRT_SIZES)->map(fn ($size) => [
            'name' => $size,
            'Quantidade' => $regs->filter(fn ($r) => $r->inscrito->tamanhoCamisa?->tamanho === $size)->count(),
        ])->values();

        return view('livewire.admin.relatorios', [
            'totalInscricoes' => $regs->count(),
            'occupancyRate' => $occupancyRate,
            'confirmedCount' => $confirmed->count(),
            'maxSpots' => $maxSpots,
            'totalRevenue' => $confirmed->count() * $value,
            'pendingRevenue' => $pending->count() * $value,
            'statusData' => $statusData,
            'financialData' => $financialData,
            'ageData' => $ageData,
            'shirtData' => $shirtData,
        ]);
    }
}
