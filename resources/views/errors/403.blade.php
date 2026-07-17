<x-layouts.guest :title="'Acesso não autorizado'">
    <div class="min-h-screen bg-ignis-navy-dark text-white flex items-center justify-center px-4">
        <div class="text-center max-w-sm">
            <div class="text-6xl mb-4">🔒</div>
            <h1 class="text-2xl font-black mb-2">Acesso não autorizado</h1>
            <p class="text-slate-400 mb-8">{{ $exception->getMessage() ?: 'Sua conta não tem permissão para acessar esta área.' }}</p>
            @auth
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-ignis-gold hover:text-ignis-gold-light transition-colors text-sm">Sair e voltar ao início</button>
                </form>
            @else
                <a href="{{ route('home') }}" class="text-ignis-gold hover:text-ignis-gold-light transition-colors text-sm">← Voltar ao início</a>
            @endauth
        </div>
    </div>
</x-layouts.guest>
