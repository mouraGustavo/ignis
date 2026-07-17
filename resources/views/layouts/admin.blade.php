<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? "$title · Admin Ignis" : 'Admin Ignis' }}</title>
    <link rel="icon" href="data:,">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-ignis-navy-dark text-white min-h-screen">
    <div class="border-b border-ignis-gold/10 bg-ignis-navy">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-sm font-bold tracking-widest text-white hover:text-ignis-gold transition-colors">
                <x-icon name="flame" class="w-4 h-4 text-ignis-gold" /> IGNIS · ADMIN
            </a>
            <div class="flex items-center gap-4 text-sm text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">Site público</a>
                <span class="text-slate-600">|</span>
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="hover:text-white transition-colors">Sair</button>
                </form>
            </div>
        </div>
    </div>

    {{ $slot }}

    @livewireScripts
</body>
</html>
