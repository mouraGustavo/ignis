<?php

namespace App\View\Components;

use App\Models\EventoInscrito;
use App\Models\StatusInscricao;
use Illuminate\View\Component;
use Illuminate\View\View;

class VagasCounter extends Component
{
    public int $confirmed;

    public int $remaining;

    public float $percent;

    public bool $full;

    public function __construct(public int $maxSpots = 150)
    {
        $this->confirmed = EventoInscrito::query()
            ->whereHas('status', fn ($q) => $q->where('nome', StatusInscricao::CONFIRMADA))
            ->count();

        $this->remaining = $this->maxSpots - $this->confirmed;
        $this->percent = $this->maxSpots > 0 ? min(($this->confirmed / $this->maxSpots) * 100, 100) : 0;
        $this->full = $this->remaining <= 0;
    }

    public function render(): View
    {
        return view('components.vagas-counter');
    }
}
