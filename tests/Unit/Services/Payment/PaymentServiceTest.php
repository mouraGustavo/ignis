<?php

namespace Tests\Unit\Services\Payment;

use App\DTOs\ChargeResultDTO;
use App\Enums\PaymentStatus;
use App\Exceptions\PaymentDeclinedException;
use App\Exceptions\PaymentProcessingException;
use App\Models\Payment;
use App\Services\Efi\ChargeService;
use App\Services\Payment\PaymentService;
use App\Services\Payment\PaymentStatusService;
use App\Services\RegistrationService;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\CreatesInscricaoFixtures;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use CreatesInscricaoFixtures;

    #[Test]
    public function it_processes_approved_credit_card_payment(): void
    {
        $this->seedBaseData();
        $eventoInscrito = $this->createEventoInscrito();

        $chargeService = $this->createMock(ChargeService::class);
        $chargeService->method('createOneStepCardCharge')->willReturn(
            new ChargeResultDTO('12345', PaymentStatus::PAID, rawResponse: ['status' => 'approved'])
        );

        $registrationService = $this->createMock(RegistrationService::class);
        $registrationService->expects($this->once())->method('confirmarPagamento');

        $service = new PaymentService(
            $chargeService,
            new PaymentStatusService,
            $registrationService,
        );

        $result = $service->processCreditCard(
            $eventoInscrito,
            'token_test_abc',
            'XXXXXXXXXXXX1234',
            1,
            '52998224725',
        );

        $this->assertTrue($result->isSuccess());
        $this->assertSame(PaymentStatus::PAID, $result->status);
        $this->assertDatabaseHas('payments', [
            'evento_inscrito_id' => $eventoInscrito->id,
            'transaction_id' => '12345',
            'status' => PaymentStatus::PAID->value,
        ]);
    }

    #[Test]
    public function it_throws_when_card_is_declined(): void
    {
        $this->seedBaseData();
        $eventoInscrito = $this->createEventoInscrito();

        $chargeService = $this->createMock(ChargeService::class);
        $chargeService->method('createOneStepCardCharge')->willReturn(
            new ChargeResultDTO('12345', PaymentStatus::FAILED, 'Cartão recusado', true)
        );

        $service = new PaymentService(
            $chargeService,
            new PaymentStatusService,
            $this->createMock(RegistrationService::class),
        );

        $this->expectException(PaymentDeclinedException::class);

        $service->processCreditCard(
            $eventoInscrito,
            'token_test_abc',
            'XXXXXXXXXXXX1234',
            1,
            '52998224725',
        );
    }

    #[Test]
    public function it_prevents_duplicate_paid_payments(): void
    {
        $this->seedBaseData();
        $eventoInscrito = $this->createEventoInscrito();

        Payment::query()->create([
            'evento_inscrito_id' => $eventoInscrito->id,
            'gateway' => 'efi',
            'status' => PaymentStatus::PAID,
            'amount' => 15000,
            'installments' => 1,
            'payment_method' => 'credit_card',
            'idempotency_key' => "evento_inscrito_{$eventoInscrito->id}",
            'paid_at' => now(),
        ]);

        $service = new PaymentService(
            $this->createMock(ChargeService::class),
            new PaymentStatusService,
            $this->createMock(RegistrationService::class),
        );

        $this->expectException(PaymentProcessingException::class);

        $service->processCreditCard(
            $eventoInscrito,
            'token_test_abc',
            'XXXXXXXXXXXX1234',
            1,
            '52998224725',
        );
    }
}
