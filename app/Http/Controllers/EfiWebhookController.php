<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEfiNotificationJob;
use App\Models\PaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EfiWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $token = $request->input('notification');

        if (! is_string($token) || $token === '') {
            return response('notification token required', 400);
        }

        $notification = PaymentNotification::create([
            'token' => $token,
            'payload' => $request->all(),
        ]);

        ProcessEfiNotificationJob::dispatch($notification->id);

        return response('OK', 200);
    }
}
