<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
                <x-icon name="chevron-left" class="w-5 h-5" />
            </a>
            <h1 class="text-2xl font-black">Mídias</h1>
        </div>
        <button wire:click="$toggle('adding')" class="flex items-center gap-2 bg-ignis-gold hover:bg-ignis-gold-dark text-ignis-navy-dark px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
            <x-icon name="plus" class="w-4 h-4" /> Adicionar
        </button>
    </div>

    @if ($adding)
        <form wire:submit="add" class="ignis-card mb-6 space-y-4">
            <h2 class="font-bold text-ignis-gold">Nova mídia</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Tipo</label>
                    <select wire:model="type" class="ignis-input">
                        <option value="photo">Foto</option>
                        <option value="video">Vídeo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Título (opcional)</label>
                    <input wire:model="title" class="ignis-input" placeholder="Legenda da mídia">
                </div>
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-2">Upload de arquivo</label>
                <x-file-dropzone model="file" :accept="$type === 'video' ? 'video/*' : 'image/*'" :file="$file" accent="gold" />
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-2">— ou cole uma URL —</label>
                <input wire:model="url" class="ignis-input" placeholder="https://..." @disabled($file) >
                @error('url') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="add" class="flex items-center gap-2 bg-ignis-gold hover:bg-ignis-gold-dark text-ignis-navy-dark px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="add">Adicionar</span>
                    <span wire:loading wire:target="add" class="flex items-center gap-2"><x-icon name="loader-2" class="w-4 h-4" /> Enviando...</span>
                </button>
                <button type="button" wire:click="$set('adding', false)" class="px-5 py-2.5 rounded-xl text-sm text-slate-400 hover:text-white bg-ignis-navy-light hover:bg-slate-700 transition-colors">
                    Cancelar
                </button>
            </div>
        </form>
    @endif

    @if ($medias->isEmpty())
        <div class="text-center py-20 text-slate-500">Nenhuma mídia adicionada ainda.</div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach ($medias as $media)
                <div class="relative group rounded-xl overflow-hidden aspect-square bg-ignis-navy-light {{ ! $media->active ? 'opacity-40' : '' }}">
                    @if ($media->type === 'photo')
                        <img src="{{ $media->url }}" alt="{{ $media->title }}" class="w-full h-full object-cover">
                    @else
                        <video src="{{ $media->url }}" class="w-full h-full object-cover"></video>
                    @endif
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-2 text-center">
                        <div class="flex items-center gap-2 text-xs text-white">
                            <x-icon :name="$media->type === 'photo' ? 'image' : 'video'" class="w-4 h-4" />
                            {{ $media->type }}
                        </div>
                        @if ($media->title)
                            <p class="text-xs text-slate-300">{{ $media->title }}</p>
                        @endif
                        <button wire:click="toggleActive({{ $media->id }})" class="text-xs px-3 py-1 rounded-full border transition-colors {{ $media->active ? 'border-amber-500 text-amber-400 hover:bg-amber-500/20' : 'border-green-500 text-green-400 hover:bg-green-500/20' }}">
                            {{ $media->active ? 'Ocultar' : 'Mostrar' }}
                        </button>
                        <button wire:click="delete({{ $media->id }})" wire:confirm="Remover esta mídia?" class="text-xs px-3 py-1 rounded-full border border-red-500 text-red-400 hover:bg-red-500/20 transition-colors flex items-center gap-1">
                            <x-icon name="trash-2" class="w-3 h-3" /> Remover
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
