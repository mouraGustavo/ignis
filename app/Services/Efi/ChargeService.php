<?php

namespace App\Services\Efi;

use App\DTOs\ChargeResultDTO;
use App\DTOs\CreateCreditCardChargeDTO;
use App\Enums\PaymentStatus;

class ChargeService
{
    public function __construct(
        private readonly EfiClient $efiClient,
    ) {
    }

    public function createOneStepCardCharge(CreateCreditCardChargeDTO $dto): ChargeResultDTO
    {
        $notificationUrl = config('efi.notification_url') ?: route('webhooks.efi');

        $body = [
            'items' => [[
                'name' => $dto->itemName,
                'value' => $dto->amountCents,
                'amount' => 1,
            ]],
            'metadata' => [
                'notification_url' => $notificationUrl,
                'custom_id' => $dto->customId,
            ],
            'payment' => [
                'credit_card' => [
                    'customer' => $dto->customer->toEfiArray(),
                    'installments' => $dto->installments,
                    'payment_token' => $dto->paymentToken,
                ],
            ],
        ];

        $response = $this->efiClient->call(
            fn ($api) => $api->createOneStepCharge([], $body)
        );

        return $this->mapChargeResponse($response);
    }

    public function getCharge(string $chargeId): ChargeResultDTO
    {
        $response = $this->efiClient->call(
            fn ($api) => $api->detailCharge(['id' => $chargeId])
        );

        return $this->mapChargeResponse($response);
    }

    public function cancelCharge(string $chargeId): void
    {
        $this->efiClient->call(
            fn ($api) => $api->cancelCharge(['id' => $chargeId])
        );
    }

    public function refundCharge(string $chargeId, ?int $amountCents = null): void
    {
        $params = ['id' => $chargeId];
        $body = $amountCents !== null ? ['amount' => $amountCents] : [];

        $this->efiClient->call(
            fn ($api) => $api->refundCard($params, $body)
        );
    }

    public function retryCharge(string $chargeId, CreateCreditCardChargeDTO $dto): ChargeResultDTO
    {
        $body = [
            'payment' => [
                'credit_card' => [
                    'customer' => $dto->customer->toEfiArray(),
                    'installments' => $dto->installments,
                    'payment_token' => $dto->paymentToken,
                    'update_card' => true,
                ],
            ],
        ];

        $response = $this->efiClient->call(
            fn ($api) => $api->cardPaymentRetry(['id' => $chargeId], $body)
        );

        return $this->mapChargeResponse($response);
    }

    private function mapChargeResponse(mixed $response): ChargeResultDTO
    {
        if (is_object($response)) {
            $response = json_decode(json_encode($response), true);
        }

        if (! is_array($response)) {
            return new ChargeResultDTO(
                chargeId: null,
                status: PaymentStatus::FAILED,
                refusalReason: 'Resposta inválida da API Efí',
            );
        }

        $data = $response['data'] ?? $response;
        $data = (array) $data;

        $efiStatus = (string) ($data['status'] ?? 'pending');
        $status = PaymentStatus::fromEfiStatus($efiStatus);

        $refusal = $data['refusal'] ?? null;
        $refusalReason = null;
        $retryable = false;

        if (is_array($refusal)) {
            $refusalReason = $refusal['reason'] ?? null;
            $retryable = (bool) ($refusal['retry'] ?? false);
        } elseif (isset($data['reason'])) {
            $refusalReason = (string) $data['reason'];
        }

        $chargeId = $data['charge_id'] ?? $data['id'] ?? null;

        return new ChargeResultDTO(
            chargeId: $chargeId !== null ? (string) $chargeId : null,
            status: $status,
            refusalReason: $refusalReason,
            retryable: $retryable,
            rawResponse: $data,
        );
    }
}
