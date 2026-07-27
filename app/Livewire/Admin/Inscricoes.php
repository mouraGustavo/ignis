<?php

namespace App\Livewire\Admin;

use App\Models\EventoInscrito;
use App\Models\Setting;
use App\Models\StatusInscricao;
use App\Services\RegistrationService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Inscricoes extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterStatus = 'all';

    public ?int $selectedId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function show(int $id): void
    {
        $this->selectedId = $id;
    }

    public function closeModal(): void
    {
        $this->selectedId = null;
    }

    public function confirm(int $id, RegistrationService $registrationService): void
    {
        $registrationService->confirmarPagamento(EventoInscrito::query()->findOrFail($id));
    }

    public function cancel(int $id, RegistrationService $registrationService): void
    {
        $registrationService->cancelarInscricao(EventoInscrito::query()->findOrFail($id));
    }

    #[Computed]
    public function selected(): ?EventoInscrito
    {
        if (! $this->selectedId) {
            return null;
        }

        return EventoInscrito::query()
            ->with(['inscrito.contato', 'inscrito.informacaoSaude', 'inscrito.responsavel', 'status', 'evento', 'payment'])
            ->find($this->selectedId);
    }

    #[Computed]
    public function registrations()
    {
        return EventoInscrito::query()
            ->with(['inscrito.contato', 'status', 'payment'])
            ->when($this->filterStatus !== 'all', fn ($q) => $q->whereHas('status', fn ($qs) => $qs->where('nome', $this->filterStatus)))
            ->when($this->search !== '', function ($q) {
                $q->whereHas('inscrito', function ($qi) {
                    $qi->where('nome', 'ilike', "%{$this->search}%")
                        ->orWhereHas('contato', function ($qc) {
                            $qc->where('email', 'ilike', "%{$this->search}%")
                                ->orWhere('telefone', 'ilike', "%{$this->search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.admin.inscricoes', [
            'maxSpots' => (int) Setting::get('max_spots', '150'),
            'confirmedCount' => EventoInscrito::query()
                ->whereHas('status', fn ($q) => $q->where('nome', StatusInscricao::CONFIRMADA))
                ->count(),
        ]);
    }
}
