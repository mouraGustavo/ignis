<?php

namespace App\Services\Payment;

use App\DTOs\ChargeResultDTO;
use App\DTOs\CreateCreditCardChargeDTO;
use App\DTOs\CreditCardCustomerDTO;
use App\DTOs\PaymentResultDTO;
use App\Enums\PaymentGateway;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\PaymentFailed;
use App\Events\PaymentProcessed;
use App\Exceptions\EfiApiException;
use App\Exceptions\PaymentDeclinedException;
use App\Exceptions\PaymentProcessingException;
use App\Models\EventoInscrito;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\StatusInscricao;
use App\Services\Efi\ChargeService;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private readonly ChargeService $chargeService,
        private readonly PaymentStatusService $paymentStatusService,
        private readonly RegistrationService $registrationService,
    ) {
    }

    public function processCreditCard(
        EventoInscrito $eventoInscrito,
        string $paymentToken,
        string $cardMask,
        int $installments,
        string $holderDocument,
    ): PaymentResultDTO {
        $eventoInscrito->load(['inscrito.contato', 'status', 'evento', 'payment']);

        $this->validateInscricao($eventoInscrito);

        $amountCents = $this->resolveAmountCents();
        $idempotencyKey = "evento_inscrito_{$eventoInscrito->id}";

        $existingPaid = Payment::query()
            ->where('evento_inscrito_id', $eventoInscrito->id)
            ->where('status', PaymentStatus::PAID)
            ->exists();

        if ($existingPaid || $eventoInscrito->pagamento_confirmado) {
            throw new PaymentProcessingException('Esta inscrição já possui pagamento confirmado.');
        }

        $customer = new CreditCardCustomerDTO(
            name: $eventoInscrito->inscrito->nome,
            cpf: $holderDocument,
            email: $eventoInscrito->inscrito->contato->email,
            phoneNumber: $eventoInscrito->inscrito->contato->telefone ?? '',
        );

        $chargeDto = new CreateCreditCardChargeDTO(
            eventoInscritoId: $eventoInscrito->id,
            paymentToken: $paymentToken,
            cardMask: $cardMask,
            installments: $installments,
            amountCents: $amountCents,
            customer: $customer,
            itemName: 'Inscrição ' . ($eventoInscrito->evento->titulo ?? 'Ignis'),
            customId: $eventoInscrito->numero_inscricao ?? (string) $eventoInscrito->id,
        );

        return DB::transaction(function () use ($eventoInscrito, $chargeDto, $idempotencyKey, $amountCents, $cardMask, $installments) {
            $payment = Payment::query()
                ->where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($payment && $payment->isPaid()) {
                throw new PaymentProcessingException('Pagamento já confirmado para esta inscrição.');
            }

            if ($payment && $payment->status === PaymentStatus::PENDING && $payment->created_at?->gt(now()->subMinutes(5))) {
                throw new PaymentProcessingException('Um pagamento já está em processamento. Aguarde alguns instantes.');
            }

            if (! $payment) {
                $payment = Payment::create([
                    'evento_inscrito_id' => $eventoInscrito->id,
                    'gateway' => PaymentGateway::EFI,
                    'status' => PaymentStatus::PENDING,
                    'amount' => $amountCents,
                    'installments' => $installments,
                    'payment_method' => PaymentMethod::CREDIT_CARD,
                    'card_mask' => $cardMask,
                    'idempotency_key' => $idempotencyKey,
                ]);
            } else {
                $this->paymentStatusService->assertCanTransition($payment->status, PaymentStatus::PENDING);

                $payment->update([
                    'status' => PaymentStatus::PENDING,
                    'amount' => $amountCents,
                    'installments' => $installments,
                    'card_mask' => $cardMask,
                    'failure_reason' => null,
                ]);
            }

            $eventoInscrito->update([
                'metodo_pagamento' => 'credit_card',
                'parcelas_pagamento' => $installments,
                'payment_id' => $payment->id,
            ]);

            try {
                $chargeResult = $payment->transaction_id && $payment->isFailed()
                    ? $this->chargeService->retryCharge($payment->transaction_id, $chargeDto)
                    : $this->chargeService->createOneStepCardCharge($chargeDto);
            } catch (EfiApiException $e) {
                $payment->update([
                    'status' => PaymentStatus::FAILED,
                    'failure_reason' => $e->getMessage(),
                ]);

                PaymentFailed::dispatch($payment->fresh(), $e->getMessage());

                throw new PaymentProcessingException(
                    $e->isTemporary()
                        ? 'Serviço de pagamento temporariamente indisponível. Tente novamente em instantes.'
                        : 'Não foi possível processar o pagamento. Verifique os dados e tente novamente.'
                );
            }

            return $this->applyChargeResult($eventoInscrito, $payment, $chargeResult);
        });
    }

    public function reconcileFromChargeResult(Payment $payment, ChargeResultDTO $chargeResult): PaymentResultDTO
    {
        $eventoInscrito = $payment->eventoInscrito()->firstOrFail();

        return DB::transaction(function () use ($payment, $chargeResult, $eventoInscrito) {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            return $this->applyChargeResult($eventoInscrito, $payment, $chargeResult);
        });
    }

    public function reconcileFromEfiStatus(Payment $payment, string $efiStatus, ?array $raw = null): void
    {
        $status = PaymentStatus::fromEfiStatus($efiStatus);

        if (! $this->paymentStatusService->canTransition($payment->status, $status)) {
            return;
        }

        $payment->update([
            'status' => $status,
            'gateway_response' => $raw ?? $payment->gateway_response,
            'paid_at' => $status === PaymentStatus::PAID ? now() : $payment->paid_at,
        ]);

        if ($status === PaymentStatus::PAID) {
            $this->confirmInscricaoIfNeeded($payment->eventoInscrito);
            PaymentProcessed::dispatch($payment->fresh());
        }
    }

    private function applyChargeResult(
        EventoInscrito $eventoInscrito,
        Payment $payment,
        ChargeResultDTO $chargeResult,
    ): PaymentResultDTO {
        $this->paymentStatusService->assertCanTransition($payment->status, $chargeResult->status);

        $payment->update([
            'transaction_id' => $chargeResult->chargeId,
            'status' => $chargeResult->status,
            'failure_reason' => $chargeResult->refusalReason,
            'gateway_response' => $chargeResult->rawResponse,
            'paid_at' => $chargeResult->isApproved() ? now() : null,
        ]);

        if ($chargeResult->isApproved()) {
            $this->confirmInscricaoIfNeeded($eventoInscrito);
            PaymentProcessed::dispatch($payment->fresh());

            return new PaymentResultDTO(
                payment: $payment->fresh(),
                status: PaymentStatus::PAID,
                message: 'Pagamento aprovado! Sua inscrição foi confirmada.',
            );
        }

        if ($chargeResult->isWaiting()) {
            $this->setAguardandoPagamento($eventoInscrito);
            PaymentProcessed::dispatch($payment->fresh());

            return new PaymentResultDTO(
                payment: $payment->fresh(),
                status: PaymentStatus::AUTHORIZED,
                message: 'Pagamento em processamento. Você receberá a confirmação por e-mail.',
            );
        }

        PaymentFailed::dispatch($payment->fresh(), $chargeResult->refusalReason);

        throw new PaymentDeclinedException(
            $chargeResult->refusalReason ?? 'Pagamento recusado. Verifique os dados do cartão ou utilize outro meio de pagamento.',
            $chargeResult->retryable,
        );
    }

    private function validateInscricao(EventoInscrito $eventoInscrito): void
    {
        if ($eventoInscrito->status->nome !== StatusInscricao::PENDENTE
            && $eventoInscrito->status->nome !== StatusInscricao::AGUARDANDO_PAGAMENTO) {
            throw new PaymentProcessingException('Esta inscrição não está elegível para pagamento.');
        }

        if ($eventoInscrito->evento?->atingiuLimiteVagas()) {
            throw new PaymentProcessingException('As vagas para este evento estão esgotadas.');
        }
    }

    private function confirmInscricaoIfNeeded(EventoInscrito $eventoInscrito): void
    {
        if ($eventoInscrito->pagamento_confirmado) {
            return;
        }

        $this->registrationService->confirmarPagamento($eventoInscrito);
    }

    private function setAguardandoPagamento(EventoInscrito $eventoInscrito): void
    {
        $status = StatusInscricao::query()
            ->where('nome', StatusInscricao::AGUARDANDO_PAGAMENTO)
            ->first();

        if ($status) {
            $eventoInscrito->update(['status_id' => $status->id]);
        }
    }

    private function resolveAmountCents(): int
    {
        $value = (float) Setting::get('registration_value', '150');

        return (int) round($value * 100);
    }
}
