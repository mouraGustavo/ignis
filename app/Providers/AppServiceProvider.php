<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('efi-webhook', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Permite `<x-layouts.guest>`/`<x-layouts.admin>` resolverem para
        // resources/views/layouts/*.blade.php (usados tanto via tag de componente
        // nas views simples quanto via #[Layout('layouts.guest')] nos componentes Livewire).
        Blade::anonymousComponentPath(resource_path('views'));
    }
}
