<?php

use App\Http\Controllers\EfiWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhooks externos (sem autenticação de sessão / CSRF)
|--------------------------------------------------------------------------
| Endpoints stateless para provedores externos (ex.: Efí Bank). A segurança
| vem da validação do payload, rate limiting e da resolução do token via API
| oficial da Efí no job assíncrono — não de cookies ou CSRF.
*/

Route::post('/webhooks/efi', EfiWebhookController::class)
    ->middleware('throttle:efi-webhook')
    ->name('webhooks.efi');
