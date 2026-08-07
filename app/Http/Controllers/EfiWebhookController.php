<?php

namespace App\Http\Controllers;

use App\Http\Requests\EfiWebhookRequest;
use App\Jobs\ProcessEfiNotificationJob;
use App\Models\PaymentNotification;
use Illuminate\Http\Response;

class EfiWebhookController extends Controller
{
    public function __invoke(EfiWebhookRequest $request): Response
    {
        $token = $request->validated('notification');

        $existing = PaymentNotification::query()
            ->where('token', $token)
            ->first();

        if ($existing !== null) {
            return response('OK', 200);
        }

        $notification = PaymentNotification::create([
            'token' => $token,
            'payload' => $request->validated(),
        ]);

        ProcessEfiNotificationJob::dispatch($notification->id);

        return response('OK', 200);
    }
}
