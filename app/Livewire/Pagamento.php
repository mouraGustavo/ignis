<?php

namespace App\Livewire;

use App\Models\EventoInscrito;
use App\Models\Setting;
use App\Services\FileUploadService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.guest')]
class Pagamento extends Component
{
    use WithFileUploads;

    public EventoInscrito $eventoInscrito;

    public string $method = 'pix';

    public $proofFile = null;

    public int $installments = 1;

    public bool $done = false;

    public ?string $formError = null;

    public array $settings = [];

    public function mount(EventoInscrito $eventoInscrito): void
    {
        $this->eventoInscrito = $eventoInscrito->load(['inscrito.contato', 'status', 'evento']);
        $this->settings = Setting::allAsArray();
    }

    protected function rules(): array
    {
        return [
            'method' => ['required', Rule::in(['pix', 'credit_card'])],
            'proofFile' => [
                Rule::requiredIf(fn () => $this->method === 'pix'),
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120',
            ],
            'installments' => [
                Rule::requiredIf(fn () => $this->method === 'credit_card'),
                'nullable', 'integer', 'min:1', 'max:2',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'proofFile.required' => 'Envie o comprovante de pagamento PIX.',
            'installments.max' => 'O pagamento no cartão pode ser parcelado em no máximo 2x.',
        ];
    }

    public function submit(FileUploadService $fileUploadService): void
    {
        $this->formError = null;
        $this->validate();

        $update = ['metodo_pagamento' => $this->method];

        if ($this->method === 'pix' && $this->proofFile) {
            $update['comprovante_pagamento_url'] = $fileUploadService->storePrivate(
                $this->proofFile,
                'pagamentos/comprovantes'
            );
        }

        if ($this->method === 'credit_card') {
            $update['parcelas_pagamento'] = $this->installments;
        }

        $this->eventoInscrito->update($update);
        $this->done = true;
    }

    public function render()
    {
        return view('livewire.pagamento');
    }
}
