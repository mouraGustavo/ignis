<?php

namespace App\Livewire;

use App\Models\EventoInscrito;
use App\Models\StatusInscricao;
use App\Models\TamanhoCamisa;
use App\Services\RegistrationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.guest')]
class MinhaInscricao extends Component
{
    use WithFileUploads;

    #[Url]
    public string $email = '';

    public bool $searched = false;

    public ?Collection $results = null;

    // --- Edicao ---
    public ?int $editingId = null;

    public string $editFullName = '';

    public string $editPhone = '';

    public string $editBirthDate = '';

    public string $editShirtSize = '';

    public string $editFoodRestrictions = '';

    public string $editAllergies = '';

    public string $editMedications = '';

    public string $editHealthConditions = '';

    public string $editEmergencyContactName = '';

    public string $editEmergencyContactPhone = '';

    public string $editGuardianName = '';

    public string $editGuardianPhone = '';

    public $editGuardianDocument = null;

    public ?string $existingGuardianDocumentUrl = null;

    public ?int $editAge = null;

    public function mount(): void
    {
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->search();
        }
    }

    public function search(): void
    {
        $this->validate(['email' => ['required', 'email']]);

        $this->results = EventoInscrito::query()
            ->whereHas('inscrito.contato', fn ($q) => $q->where('email', trim(strtolower($this->email))))
            ->with(['inscrito.contato', 'inscrito.informacaoSaude', 'inscrito.responsavel', 'status', 'evento'])
            ->latest('id')
            ->get();

        $this->searched = true;
    }

    public function canEdit(EventoInscrito $reg): bool
    {
        if (in_array($reg->status->nome, [StatusInscricao::CANCELADA, StatusInscricao::RECUSADA], true)) {
            return false;
        }

        return $reg->evento !== null && ! $reg->evento->prazoInscricaoEncerrado();
    }

    public function edit(int $id): void
    {
        $reg = $this->results?->firstWhere('id', $id);

        if (! $reg || ! $this->canEdit($reg)) {
            return;
        }

        $this->resetValidation();

        $this->editingId = $reg->id;
        $this->editFullName = $reg->inscrito->nome;
        $this->editPhone = $reg->inscrito->contato->telefone ?? '';
        $this->editBirthDate = $reg->inscrito->data_nascimento->toDateString();
        $this->editShirtSize = $reg->inscrito->tamanhoCamisa?->tamanho ?? '';
        $this->editFoodRestrictions = $reg->inscrito->informacaoSaude->restricoes_alimentares ?? '';
        $this->editAllergies = $reg->inscrito->informacaoSaude->alergias ?? '';
        $this->editMedications = $reg->inscrito->informacaoSaude->medicamentos ?? '';
        $this->editHealthConditions = $reg->inscrito->informacaoSaude->condicao_saude ?? '';
        $this->editEmergencyContactName = $reg->inscrito->contato->nome_emergencia ?? '';
        $this->editEmergencyContactPhone = $reg->inscrito->contato->telefone_emergencia ?? '';
        $this->editGuardianName = $reg->inscrito->responsavel->nome ?? '';
        $this->editGuardianPhone = $reg->inscrito->responsavel->telefone ?? '';
        $this->editGuardianDocument = null;
        $this->existingGuardianDocumentUrl = $reg->inscrito->responsavel->documento_url ?? null;
        $this->editAge = Carbon::parse($this->editBirthDate)->age;
    }

    public function updatedEditBirthDate(?string $value): void
    {
        $this->editAge = null;

        if ($value) {
            try {
                $this->editAge = Carbon::parse($value)->age;
            } catch (\Throwable) {
                $this->editAge = null;
            }
        }
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetValidation();
    }

    protected function isEditingMenorDeIdade(): bool
    {
        return $this->editAge !== null && $this->editAge < 18;
    }

    protected function rulesForEdit(): array
    {
        $isMenor = $this->isEditingMenorDeIdade();

        return [
            'editFullName' => ['required', 'string', 'max:255'],
            'editPhone' => ['required', 'string', 'max:20'],
            'editBirthDate' => ['required', 'date', 'before:today'],
            'editShirtSize' => ['required', Rule::in(Inscricao::SHIRT_SIZES)],

            'editFoodRestrictions' => ['nullable', 'string', 'max:2000'],
            'editAllergies' => ['nullable', 'string', 'max:2000'],
            'editMedications' => ['nullable', 'string', 'max:2000'],
            'editHealthConditions' => ['nullable', 'string', 'max:2000'],
            'editEmergencyContactName' => ['nullable', 'string', 'max:255'],
            'editEmergencyContactPhone' => ['nullable', 'string', 'max:20'],

            'editGuardianName' => [$isMenor ? 'required' : 'nullable', 'string', 'max:255'],
            'editGuardianPhone' => ['nullable', 'string', 'max:20'],
            'editGuardianDocument' => [
                $isMenor && ! $this->existingGuardianDocumentUrl ? 'required' : 'nullable',
                'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120',
            ],
        ];
    }

    protected function messagesForEdit(): array
    {
        return [
            'editGuardianName.required' => 'O nome do responsável é obrigatório para menores de 18 anos.',
            'editGuardianDocument.required' => 'O documento do responsável é obrigatório para menores de 18 anos.',
        ];
    }

    public function saveEdit(RegistrationService $registrationService): void
    {
        $reg = $this->results?->firstWhere('id', $this->editingId);

        if (! $reg || ! $this->canEdit($reg)) {
            $this->cancelEdit();

            return;
        }

        $this->validate($this->rulesForEdit(), $this->messagesForEdit());

        $shirtSizeId = TamanhoCamisa::query()->where('tamanho', $this->editShirtSize)->value('id');

        $registrationService->atualizarDados($reg, [
            'full_name' => $this->editFullName,
            'birth_date' => $this->editBirthDate,
            'shirt_size_id' => $shirtSizeId,
            'phone' => $this->editPhone,
            'emergency_contact_name' => $this->editEmergencyContactName ?: null,
            'emergency_contact_phone' => $this->editEmergencyContactPhone ?: null,
            'food_restrictions' => $this->editFoodRestrictions ?: null,
            'allergies' => $this->editAllergies ?: null,
            'medications' => $this->editMedications ?: null,
            'health_conditions' => $this->editHealthConditions ?: null,
            'guardian_name' => $this->editGuardianName ?: null,
            'guardian_phone' => $this->editGuardianPhone ?: null,
            'guardian_document' => $this->editGuardianDocument,
        ]);

        $this->cancelEdit();
        $this->search();
    }

    public function render()
    {
        return view('livewire.minha-inscricao');
    }
}
