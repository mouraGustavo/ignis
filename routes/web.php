<?php

use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\ReportExportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EfiWebhookController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site publico
|--------------------------------------------------------------------------
| Home e Equipe sao paginas somente-leitura (sem estado de formulario), por
| isso ficam em controllers + Blade normais. As demais paginas publicas tem
| formularios/interacao e viram componentes Livewire full-page.
*/

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/equipe', [PublicSiteController::class, 'equipe'])->name('equipe');

Route::get('/inscricao', \App\Livewire\Inscricao::class)->name('inscricao');
Route::get('/pagamento/{eventoInscrito}', \App\Livewire\Pagamento::class)->name('pagamento');
Route::get('/minha-inscricao', \App\Livewire\MinhaInscricao::class)->name('minha-inscricao');

Route::post('/webhooks/efi', EfiWebhookController::class)
    ->name('webhooks.efi')
    ->withoutMiddleware([ValidateCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| Autenticacao (sessao) e area administrativa
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [PublicSiteController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/inscricoes', \App\Livewire\Admin\Inscricoes::class)->name('inscricoes');
    Route::get('/configuracoes', \App\Livewire\Admin\Configuracoes::class)->name('configuracoes');
    Route::get('/midias', \App\Livewire\Admin\Midias::class)->name('midias');
    Route::get('/equipe', \App\Livewire\Admin\Equipe::class)->name('equipe');
    Route::get('/relatorios', \App\Livewire\Admin\Relatorios::class)->name('relatorios');

    Route::get('/reports/export-csv', ReportExportController::class)->name('reports.export-csv');
    Route::get('/files', [FileController::class, 'show'])->name('files.show');
});
