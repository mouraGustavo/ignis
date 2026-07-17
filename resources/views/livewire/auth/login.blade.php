<div class="min-h-screen bg-ignis-navy-dark text-white flex items-center justify-center px-4 py-10">
    <div class="max-w-sm w-full">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-ignis-gold/10 border border-ignis-gold/30 flex items-center justify-center mx-auto mb-4">
                <x-icon name="log-in" class="w-7 h-7 text-ignis-gold" />
            </div>
            <h1 class="text-2xl font-black">Acesso Administrativo</h1>
            <p class="text-slate-400 text-sm mt-1">Entre com sua conta para gerenciar o acampamento.</p>
        </div>

        <div class="ignis-card">
            @if ($error)
                <div class="flex items-center gap-2 text-red-400 bg-red-500/10 border border-red-500/20 rounded-xl p-3 mb-5 text-sm">
                    <x-icon name="alert-circle" class="w-4 h-4 flex-shrink-0" />
                    <span>{{ $error }}</span>
                </div>
            @endif

            <form wire:submit="login" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">E-mail</label>
                    <div class="relative">
                        <x-icon name="mail" class="w-4 h-4 absolute left-4 top-3.5 text-slate-500" />
                        <input
                            id="email"
                            type="email"
                            wire:model="email"
                            autocomplete="email"
                            autofocus
                            required
                            placeholder="voce@exemplo.com"
                            class="ignis-input pl-11"
                        >
                    </div>
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Senha</label>
                    <div class="relative">
                        <x-icon name="lock" class="w-4 h-4 absolute left-4 top-3.5 text-slate-500" />
                        <input
                            id="password"
                            type="password"
                            wire:model="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="ignis-input pl-11"
                        >
                    </div>
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-400 cursor-pointer">
                    <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded border-slate-600 bg-ignis-navy-light accent-ignis-gold cursor-pointer">
                    Manter conectado
                </label>

                <button type="submit" class="ignis-btn-primary" wire:loading.attr="disabled" wire:target="login">
                    <span wire:loading.remove wire:target="login">Entrar</span>
                    <span wire:loading wire:target="login" class="flex items-center gap-2">
                        <x-icon name="loader-2" class="w-5 h-5" /> Entrando...
                    </span>
                </button>
            </form>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:text-white transition-colors inline-flex items-center gap-1">
                <x-icon name="chevron-left" class="w-4 h-4" /> Voltar ao site
            </a>
        </div>
    </div>
</div>
