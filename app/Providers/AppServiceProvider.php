<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
        // Permite `<x-layouts.guest>`/`<x-layouts.admin>` resolverem para
        // resources/views/layouts/*.blade.php (usados tanto via tag de componente
        // nas views simples quanto via #[Layout('layouts.guest')] nos componentes Livewire).
        Blade::anonymousComponentPath(resource_path('views'));
    }
}
