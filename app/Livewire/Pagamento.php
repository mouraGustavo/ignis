<?php

namespace App\Livewire;

use App\Enums\PaymentStatus;
use App\Exceptions\PaymentDeclinedException;
use App\Exceptions\PaymentProcessingException;
use App\Models\EventoInscrito;
use App\Models\Setting;
use App\Services\FileUploadService;
use App\Services\Payment\PaymentService;
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

    public bool $done = false;

    public ?string $formError = null;

    public ?string $successMessage = null;

    public bool $paymentConfirmed = false;

    public bool $paymentRetryable = false;

    public array $settings = [];

    public function mount(EventoInscrito $eventoInscrito): void
    {
        $this->eventoInscrito = $eventoInscrito->load(['inscrito.contato', 'status', 'evento', 'payment']);
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
        ];
    }

    protected function messages(): array
    {
        return [
            'proofFile.required' => 'Envie o comprovante de pagamento PIX.',
        ];
    }

    public function submit(FileUploadService $fileUploadService): void
    {
        $this->formError = null;
        $this->paymentRetryable = false;
        $this->validate();

        if ($this->method === 'credit_card') {
            $this->formError = 'Use o botão de pagamento para processar o cartão.';

            return;
        }

        $update = ['metodo_pagamento' => $this->method];

        if ($this->method === 'pix' && $this->proofFile) {
            $update['comprovante_pagamento_url'] = $fileUploadService->storePrivate(
                $this->proofFile,
                'pagamentos/comprovantes'
            );
        }

        $this->eventoInscrito->update($update);
        $this->successMessage = 'Sua inscrição está pendente de confirmação. Assim que o pagamento for validado, você receberá um e-mail.';
        $this->done = true;
    }

    public function processCardPayment(
        string $paymentToken,
        string $cardMask,
        int $installments,
        string $holderDocument,
        PaymentService $paymentService,
    ): void {
        $this->formError = null;
        $this->paymentRetryable = false;
        $this->successMessage = null;

        $this->validate([
            'method' => ['required', Rule::in(['credit_card'])],
        ]);

        try {
            $result = $paymentService->processCreditCard(
                $this->eventoInscrito,
                $paymentToken,
                $cardMask,
                $installments,
                $holderDocument,
            );

            $this->eventoInscrito->refresh()->load(['inscrito.contato', 'status', 'payment']);
            $this->paymentConfirmed = $result->status === PaymentStatus::PAID;
            $this->successMessage = $result->message;
            $this->done = true;
        } catch (PaymentDeclinedException $e) {
            $this->formError = $e->getMessage();
            $this->paymentRetryable = $e->retryable;
        } catch (PaymentProcessingException $e) {
            $this->formError = $e->getMessage();
        } catch (\Throwable $e) {
            report($e);
            $this->formError = 'Ocorreu um erro inesperado. Tente novamente ou utilize PIX.';
        }
    }

    public function registrationValueCents(): int
    {
        return (int) round(((float) ($this->settings['registration_value'] ?? '150')) * 100);
    }

    public function render()
    {
        return view('livewire.pagamento', [
            'value' => (float) ($this->settings['registration_value'] ?? '150'),
            'valueCents' => $this->registrationValueCents(),
            'efiPayeeCode' => config('efi.payee_code'),
            'efiEnvironment' => config('efi.sandbox') ? 'sandbox' : 'production',
        ]);
    }
}
