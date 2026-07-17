<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
                <x-icon name="chevron-left" class="w-5 h-5" />
            </a>
            <h1 class="text-2xl font-black">Equipe Organizadora</h1>
        </div>
        <button wire:click="$toggle('adding')" class="flex items-center gap-2 bg-ignis-gold hover:bg-ignis-gold-dark px-4 py-2 rounded-xl text-sm font-semibold text-ignis-navy-dark transition-colors">
            <x-icon name="plus" class="w-4 h-4" /> Adicionar
        </button>
    </div>

    @if ($adding)
        <form wire:submit="add" class="ignis-card mb-6 space-y-4">
            <h2 class="font-bold text-ignis-gold">Novo membro</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Nome *</label>
                    <input wire:model="name" required class="ignis-input" placeholder="Nome completo">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Função *</label>
                    <input wire:model="role" required class="ignis-input" placeholder="Ex: Coordenador geral">
                    @error('role') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-2">Bio (opcional)</label>
                <textarea wire:model="bio" class="ignis-input resize-none h-20" placeholder="Breve descrição sobre o membro..."></textarea>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-2">Foto</label>
                <x-file-dropzone model="photo" accept="image/*" :file="$photo" accent="gold" />
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="add" class="flex items-center gap-2 bg-ignis-gold hover:bg-ignis-gold-dark px-5 py-2.5 rounded-xl text-sm font-semibold text-ignis-navy-dark transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="add">Adicionar</span>
                    <span wire:loading wire:target="add" class="flex items-center gap-2"><x-icon name="loader-2" class="w-4 h-4" /> Enviando...</span>
                </button>
                <button type="button" wire:click="$set('adding', false)" class="px-5 py-2.5 rounded-xl text-sm text-slate-400 hover:text-white bg-ignis-navy-light hover:bg-slate-700 transition-colors">
                    Cancelar
                </button>
            </div>
        </form>
    @endif

    @if ($members->isEmpty())
        <div class="text-center py-20 text-slate-500">Nenhum membro adicionado ainda.</div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach ($members as $member)
                <div class="relative group rounded-xl overflow-hidden aspect-square bg-ignis-navy-light">
                    @if ($member->photo_url)
                        <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <span class="text-3xl font-black text-ignis-gold/30">{{ mb_substr($member->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent flex flex-col justify-end p-3">
                        <p class="font-bold text-sm text-white">{{ $member->name }}</p>
                        <p class="text-xs text-ignis-gold">{{ $member->role }}</p>
                    </div>
                    <button wire:click="delete({{ $member->id }})" wire:confirm="Remover este membro da equipe?" class="absolute top-2 right-2 p-1.5 rounded-lg bg-red-500/80 hover:bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                        <x-icon name="trash-2" class="w-3.5 h-3.5" />
                    </button>
                </div>
            @endforeach
        </div>
    @endif
</div>
