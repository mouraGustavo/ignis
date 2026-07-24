<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Services\Efi\ChargeService;
use App\Services\Efi\NotificationService;
use App\Services\Payment\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessEfiNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int $paymentNotificationId,
    ) {
    }

    public function handle(
        NotificationService $notificationService,
        PaymentService $paymentService,
        ChargeService $chargeService,
    ): void {
        $notification = PaymentNotification::query()->find($this->paymentNotificationId);

        if (! $notification || $notification->processed) {
            return;
        }

        try {
            $events = $notificationService->resolveNotification($notification->token);

            foreach ($events as $event) {
                $event = (array) $event;
                $chargeId = (string) ($event['identifiers']['charge_id'] ?? $event['charge_id'] ?? '');

                if ($chargeId === '') {
                    continue;
                }

                $payment = Payment::query()
                    ->where('transaction_id', $chargeId)
                    ->first();

                if (! $payment) {
                    continue;
                }

                $efiStatus = (string) ($event['status']['current'] ?? $event['status'] ?? '');

                if ($efiStatus !== '') {
                    $paymentService->reconcileFromEfiStatus($payment, $efiStatus, $event);
                    continue;
                }

                $chargeResult = $chargeService->getCharge($chargeId);
                $paymentService->reconcileFromChargeResult($payment, $chargeResult);
            }

            $notification->update([
                'processed' => true,
                'processed_at' => now(),
                'payload' => array_merge($notification->payload ?? [], ['resolved' => $events]),
            ]);
        } catch (\Throwable $e) {
            Log::error('Efí webhook processing failed', [
                'notification_id' => $notification->id,
                'message' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $notification->update([
                    'payload' => array_merge($notification->payload ?? [], ['error' => $e->getMessage()]),
                ]);
            }

            throw $e;
        }
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }
}
