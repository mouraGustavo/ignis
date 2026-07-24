<?php

namespace Tests\Feature\Payment;

use App\DTOs\ChargeResultDTO;
use App\DTOs\PaymentResultDTO;
use App\Enums\PaymentStatus;
use App\Livewire\Pagamento;
use App\Services\Payment\PaymentService;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\CreatesInscricaoFixtures;
use Tests\TestCase;

class CreditCardPaymentTest extends TestCase
{
    use CreatesInscricaoFixtures;

    #[Test]
    public function it_processes_card_payment_via_livewire(): void
    {
        $this->seedBaseData();
        $eventoInscrito = $this->createEventoInscrito();

        $payment = new \App\Models\Payment([
            'evento_inscrito_id' => $eventoInscrito->id,
            'gateway' => 'efi',
            'status' => PaymentStatus::PAID,
            'amount' => 15000,
            'installments' => 1,
            'payment_method' => 'credit_card',
            'idempotency_key' => "evento_inscrito_{$eventoInscrito->id}",
        ]);
        $payment->id = 1;

        $mock = $this->mock(PaymentService::class);
        $mock->shouldReceive('processCreditCard')
            ->once()
            ->andReturn(new PaymentResultDTO(
                payment: $payment,
                status: PaymentStatus::PAID,
                message: 'Pagamento aprovado!',
            ));

        Livewire::test(Pagamento::class, ['eventoInscrito' => $eventoInscrito])
            ->set('method', 'credit_card')
            ->call('processCardPayment', 'token_abc', 'XXXXXXXXXXXX9999', 1, '52998224725')
            ->assertSet('done', true)
            ->assertSet('paymentConfirmed', true);
    }
}
