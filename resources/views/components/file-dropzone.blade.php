@props(['model', 'accept' => null, 'hint' => null, 'file' => null, 'accent' => 'purple'])
@php
$accentClasses = $accent === 'gold' ? 'border-ignis-gold/60 bg-ignis-gold/5' : 'border-purple-500/60 bg-purple-500/5';
@endphp
<div wire:loading.class="opacity-60 pointer-events-none" wire:target="{{ $model }}">
    <label class="block cursor-pointer">
        <div class="border-2 border-dashed rounded-xl p-5 text-center transition-colors {{ $file ? $accentClasses : 'border-slate-700 hover:border-slate-500' }}">
            @if ($file)
                <div>
                    <div class="text-green-400 font-medium text-sm mb-1">✓ {{ $file->getClientOriginalName() }}</div>
                    <div class="text-xs text-slate-500">Clique para trocar</div>
                </div>
            @else
                <div>
                    <div wire:loading wire:target="{{ $model }}">
                        <x-icon name="loader-2" class="w-6 h-6 text-slate-500 mx-auto mb-2" />
                        <div class="text-sm text-slate-400">Enviando...</div>
                    </div>
                    <div wire:loading.remove wire:target="{{ $model }}">
                        <x-icon name="upload" class="w-6 h-6 text-slate-500 mx-auto mb-2" />
                        <div class="text-sm text-slate-400">Clique para selecionar arquivo</div>
                        @if ($hint)<div class="text-xs text-slate-600 mt-1">{{ $hint }}</div>@endif
                    </div>
                </div>
            @endif
        </div>
        <input type="file" wire:model="{{ $model }}" @if($accept) accept="{{ $accept }}" @endif class="hidden">
    </label>
    @error($model) <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
</div>
