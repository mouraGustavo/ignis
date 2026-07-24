<?php

namespace Tests\Feature\Webhooks;

use App\Jobs\ProcessEfiNotificationJob;
use App\Models\Payment;
use App\Models\PaymentNotification;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\CreatesInscricaoFixtures;
use Tests\TestCase;

class EfiWebhookTest extends TestCase
{
    use CreatesInscricaoFixtures;

    #[Test]
    public function it_accepts_webhook_and_dispatches_job(): void
    {
        Bus::fake();

        $response = $this->post(route('webhooks.efi'), [
            'notification' => '00000000-0000-0000-0000-000000000001',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('payment_notifications', [
            'token' => '00000000-0000-0000-0000-000000000001',
        ]);

        Bus::assertDispatched(ProcessEfiNotificationJob::class);
    }

    #[Test]
    public function it_skips_already_processed_notifications(): void
    {
        $this->seedBaseData();
        $eventoInscrito = $this->createEventoInscrito();

        Payment::query()->create([
            'evento_inscrito_id' => $eventoInscrito->id,
            'gateway' => 'efi',
            'transaction_id' => '999',
            'status' => \App\Enums\PaymentStatus::AUTHORIZED,
            'amount' => 15000,
            'installments' => 1,
            'payment_method' => 'credit_card',
            'idempotency_key' => "evento_inscrito_{$eventoInscrito->id}",
        ]);

        $notification = PaymentNotification::create([
            'token' => 'processed-token',
            'payload' => [],
            'processed' => true,
            'processed_at' => now(),
        ]);

        $job = new ProcessEfiNotificationJob($notification->id);
        $job->handle(
            $this->createMock(\App\Services\Efi\NotificationService::class),
            $this->createMock(\App\Services\Payment\PaymentService::class),
            $this->createMock(\App\Services\Efi\ChargeService::class),
        );

        $this->assertTrue($notification->fresh()->processed);
    }

    #[Test]
    public function it_rejects_webhook_without_token(): void
    {
        $this->post(route('webhooks.efi'), [])->assertStatus(400);
    }
}
