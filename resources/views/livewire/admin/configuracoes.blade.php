<div class="max-w-2xl mx-auto px-4 py-10">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
            <x-icon name="chevron-left" class="w-5 h-5" />
        </a>
        <h1 class="text-2xl font-black">Configurações do Site</h1>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="ignis-card space-y-5">
            <h2 class="font-bold text-ignis-gold">Informações do Evento</h2>
            <x-field label="Título do site" for="site_title">
                <input id="site_title" wire:model="site_title" class="ignis-input">
                @error('site_title') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </x-field>
            <x-field label="Subtítulo / Descrição" for="site_subtitle">
                <input id="site_subtitle" wire:model="site_subtitle" class="ignis-input">
            </x-field>
            <div class="grid grid-cols-2 gap-4">
                <x-field label="Data do evento" for="event_date">
                    <input id="event_date" type="date" wire:model="event_date" class="ignis-input">
                </x-field>
                <x-field label="Local" for="event_location">
                    <input id="event_location" wire:model="event_location" class="ignis-input" placeholder="Cidade / endereço">
                </x-field>
            </div>
            <x-field label="URL da imagem de capa (hero)" for="hero_image_url">
                <input id="hero_image_url" wire:model="hero_image_url" class="ignis-input" placeholder="https://...">
            </x-field>
        </section>

        <section class="ignis-card space-y-5">
            <h2 class="font-bold text-ignis-gold">Imagens da Marca</h2>
            <p class="text-xs text-slate-500 -mt-3">
                Armazenadas na própria aplicação. Envie um arquivo para substituir a imagem atual.
            </p>

            <div class="grid grid-cols-3 gap-4">
                <x-field label="Logo">
                    <img src="{{ $logo_file ? $logo_file->temporaryUrl() : $logo_url }}" alt="Logo" class="w-20 h-20 object-contain bg-white/5 rounded-xl mb-2">
                    <input type="file" wire:model="logo_file" accept="image/*" class="ignis-input text-xs">
                    @error('logo_file') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </x-field>
                <x-field label="Brasão">
                    <img src="{{ $brasao_file ? $brasao_file->temporaryUrl() : $brasao_url }}" alt="Brasão" class="w-20 h-20 object-contain bg-white/5 rounded-xl mb-2">
                    <input type="file" wire:model="brasao_file" accept="image/*" class="ignis-input text-xs">
                    @error('brasao_file') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </x-field>
                <x-field label="Igreja">
                    <img src="{{ $church_file ? $church_file->temporaryUrl() : $church_url }}" alt="Igreja" class="w-20 h-20 object-contain bg-white/5 rounded-xl mb-2">
                    <input type="file" wire:model="church_file" accept="image/*" class="ignis-input text-xs">
                    @error('church_file') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </x-field>
            </div>
            <p class="text-xs text-slate-500">
                As fotos padrão da galeria da home ficam em
                <a href="{{ route('admin.midias') }}" class="text-ignis-gold underline underline-offset-2">Mídias</a>,
                onde também podem ser trocadas ou removidas.
            </p>
        </section>

        <section class="ignis-card space-y-5">
            <h2 class="font-bold text-ignis-gold">Inscrições e Pagamento</h2>
            <div class="grid grid-cols-2 gap-4">
                <x-field label="Limite de vagas" for="max_spots">
                    <input id="max_spots" type="number" min="1" wire:model="max_spots" class="ignis-input">
                    @error('max_spots') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </x-field>
                <x-field label="Valor da inscrição (R$)" for="registration_value">
                    <input id="registration_value" type="number" min="0" step="0.01" wire:model="registration_value" class="ignis-input">
                    @error('registration_value') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </x-field>
            </div>
            <x-field label="Inscrições abertas">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="registration_open" class="w-5 h-5 accent-purple-500 cursor-pointer">
                    <span class="text-sm text-slate-300">Permitir novas inscrições</span>
                </label>
            </x-field>
        </section>

        <section class="ignis-card space-y-5">
            <h2 class="font-bold text-ignis-gold">PIX</h2>
            <x-field label="Chave PIX" for="pix_key">
                <input id="pix_key" wire:model="pix_key" class="ignis-input" placeholder="CPF, e-mail, telefone ou chave aleatória">
            </x-field>
            <x-field label="Nome do titular do PIX" for="pix_name">
                <input id="pix_name" wire:model="pix_name" class="ignis-input" placeholder="Nome exibido na chave">
            </x-field>
        </section>

        <section class="ignis-card space-y-5">
            <h2 class="font-bold text-ignis-gold">Notificações</h2>
            <x-field label="E-mail do coordenador geral (para notificações)" for="coordinator_email">
                <input id="coordinator_email" type="email" wire:model="coordinator_email" class="ignis-input" placeholder="coordenador@email.com">
                <p class="text-xs text-slate-500 mt-1">Recebe automaticamente a planilha de participantes quando o limite de vagas é atingido.</p>
                @error('coordinator_email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </x-field>
        </section>

        <section class="ignis-card space-y-5">
            <h2 class="font-bold text-ignis-gold">Redes Sociais</h2>
            <x-field label="URL do Instagram" for="instagram_url">
                <input id="instagram_url" wire:model="instagram_url" class="ignis-input" placeholder="https://www.instagram.com/...">
            </x-field>
        </section>

        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="ignis-btn-primary">
            <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                @if ($saved)
                    <x-icon name="check" class="w-5 h-5" /> Salvo!
                @else
                    <x-icon name="save" class="w-4 h-4" /> Salvar Configurações
                @endif
            </span>
            <span wire:loading wire:target="save" class="flex items-center gap-2">
                <x-icon name="loader-2" class="w-5 h-5" /> Salvando...
            </span>
        </button>
    </form>
</div>
