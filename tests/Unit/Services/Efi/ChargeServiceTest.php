<?php

namespace Tests\Unit\Services\Efi;

use App\DTOs\CreateCreditCardChargeDTO;
use App\DTOs\CreditCardCustomerDTO;
use App\Enums\PaymentStatus;
use App\Exceptions\EfiApiException;
use App\Services\Efi\ChargeService;
use App\Services\Efi\EfiClient;
use PHPUnit\Framework\Attributes\Test;
use Tests\UnitTestCase;

class ChargeServiceTest extends UnitTestCase
{
    #[Test]
    public function it_maps_approved_charge_response(): void
    {
        $efiClient = $this->createMock(EfiClient::class);
        $efiClient->method('call')->willReturn([
            'code' => 200,
            'data' => [
                'charge_id' => 123,
                'status' => 'approved',
                'total' => 15000,
            ],
        ]);

        $service = new ChargeService($efiClient);

        $dto = new CreateCreditCardChargeDTO(
            eventoInscritoId: 1,
            paymentToken: 'token',
            cardMask: 'XXXX',
            installments: 1,
            amountCents: 15000,
            customer: new CreditCardCustomerDTO('Test', '52998224725', 'a@b.com', '11999999999'),
            itemName: 'Inscrição',
            customId: 'INS001',
        );

        $result = $service->createOneStepCardCharge($dto);

        $this->assertSame('123', $result->chargeId);
        $this->assertSame(PaymentStatus::PAID, $result->status);
    }

    #[Test]
    public function it_propagates_efi_api_exceptions(): void
    {
        $efiClient = $this->createMock(EfiClient::class);
        $efiClient->method('call')->willThrowException(
            new EfiApiException('Timeout', 504)
        );

        $service = new ChargeService($efiClient);

        $this->expectException(EfiApiException::class);

        $service->getCharge('999');
    }
}
