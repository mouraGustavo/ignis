<div>
    @if ($isFull)
        <div class="min-h-screen bg-ignis-navy-dark flex items-center justify-center px-4">
            <div class="text-center">
                <div class="text-6xl mb-4">😔</div>
                <h2 class="text-2xl font-bold text-white mb-2">Inscrições encerradas</h2>
                <p class="text-slate-400 mb-6">As vagas foram preenchidas ou as inscrições estão fechadas no momento.</p>
                <a href="{{ route('home') }}" class="text-ignis-gold hover:text-purple-300 flex items-center gap-1 justify-center">
                    <x-icon name="chevron-left" class="w-4 h-4" /> Voltar ao início
                </a>
            </div>
        </div>
    @else
        <div class="min-h-screen bg-ignis-navy-dark text-white py-10 px-4">
            <div class="max-w-2xl mx-auto">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-slate-400 hover:text-white mb-8 text-sm transition-colors">
                    <x-icon name="chevron-left" class="w-4 h-4" /> Voltar
                </a>

                <div class="mb-8">
                    <h1 class="text-3xl font-black mb-2">Formulário de Inscrição</h1>
                    <p class="text-slate-400">Preencha todos os campos obrigatórios (*) com atenção.</p>
                </div>

                <form
                    class="space-y-8"
                    x-data="{
                        birthDate: @entangle('birth_date'),
                        get age() {
                            if (! this.birthDate) return null;
                            const dob = new Date(this.birthDate + 'T00:00:00');
                            if (isNaN(dob.getTime())) return null;
                            const today = new Date();
                            let age = today.getFullYear() - dob.getFullYear();
                            const monthDiff = today.getMonth() - dob.getMonth();
                            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                                age--;
                            }
                            return age;
                        },
                        async handleSubmit(e) {
                            e.preventDefault();
                            const token = await window.executeRecaptcha(@js(config('services.recaptcha.site_key')), 'inscricao');
                            $wire.submit(token);
                        },
                    }"
                    @submit="handleSubmit($event)"
                >
                    {{-- Dados Pessoais --}}
                    <section class="ignis-card space-y-5">
                        <h2 class="text-lg font-bold text-ignis-gold">Dados Pessoais</h2>

                        <x-field label="Nome completo *" for="full_name">
                            <input id="full_name" wire:model="full_name" required class="ignis-input" placeholder="Seu nome completo">
                            @error('full_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </x-field>

                        <div class="grid grid-cols-2 gap-4">
                            <x-field label="Telefone / WhatsApp *" for="phone">
                                <input id="phone" wire:model="phone" required class="ignis-input" placeholder="(00) 00000-0000">
                                @error('phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </x-field>
                            <x-field label="E-mail *" for="email">
                                <input id="email" type="email" wire:model="email" required class="ignis-input" placeholder="seu@email.com">
                                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </x-field>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <x-field label="Data de nascimento *" for="birth_date">
                                <input id="birth_date" type="date" x-model="birthDate" required class="ignis-input">
                                @error('birth_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </x-field>
                            <x-field label="Idade">
                                <input :value="age !== null ? age + ' anos' : ''" readonly class="ignis-input opacity-60 cursor-not-allowed" placeholder="Calculado automaticamente">
                            </x-field>
                        </div>

                        <div x-show="age !== null && age < {{ $minAge }}" x-cloak class="flex items-center gap-2 text-red-400 text-sm bg-red-500/10 border border-red-500/20 rounded-xl p-3">
                            <x-icon name="alert-circle" class="w-4 h-4 flex-shrink-0" />
                            <span>Idade mínima é {{ $minAge }} anos.</span>
                        </div>

                        <x-field label="Tamanho da camiseta *" for="shirt_size">
                            <select id="shirt_size" wire:model="shirt_size" required class="ignis-input">
                                <option value="">Selecione</option>
                                @foreach (\App\Livewire\Inscricao::SHIRT_SIZES as $size)
                                    <option value="{{ $size }}">{{ $size }}</option>
                                @endforeach
                            </select>
                            @error('shirt_size') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </x-field>
                    </section>

                    {{-- Menores de idade --}}
                    <section x-show="age !== null && age < 18" x-cloak class="bg-amber-900/20 border border-amber-700/40 rounded-2xl p-6 space-y-5">
                        <h2 class="text-lg font-bold text-amber-400">Responsável (menor de 18 anos)</h2>
                        <x-field label="Nome do responsável *" for="guardian_name">
                            <input id="guardian_name" wire:model="guardian_name" class="ignis-input" placeholder="Nome completo do responsável">
                            @error('guardian_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </x-field>
                        <x-field label="Documento do responsável (foto + assinatura) *">
                            <x-file-dropzone model="guardianDocument" accept="image/*,application/pdf" hint="Foto do documento com assinatura — JPG, PNG ou PDF" :file="$guardianDocument" />
                        </x-field>
                    </section>

                    {{-- Informacoes Medicas --}}
                    <section class="ignis-card space-y-5">
                        <h2 class="text-lg font-bold text-ignis-gold">Informações Médicas e Adicionais</h2>
                        <x-field label="Restrições alimentares" for="food_restrictions">
                            <input id="food_restrictions" wire:model="food_restrictions" class="ignis-input" placeholder="Ex: vegetariano, sem glúten...">
                        </x-field>
                        <x-field label="Alergias" for="allergies">
                            <input id="allergies" wire:model="allergies" class="ignis-input" placeholder="Ex: amendoim, látex...">
                        </x-field>
                        <x-field label="Uso contínuo de medicamentos" for="medications">
                            <input id="medications" wire:model="medications" class="ignis-input" placeholder="Nome e dosagem dos medicamentos">
                        </x-field>
                        <x-field label="Condição de saúde relevante" for="health_conditions">
                            <textarea id="health_conditions" wire:model="health_conditions" class="ignis-input resize-none h-20" placeholder="Descreva condições de saúde relevantes..."></textarea>
                        </x-field>
                        <div class="grid grid-cols-2 gap-4">
                            <x-field label="Contato de emergência (Nome)" for="emergency_contact_name">
                                <input id="emergency_contact_name" wire:model="emergency_contact_name" class="ignis-input" placeholder="Nome">
                            </x-field>
                            <x-field label="Contato de emergência (Telefone)" for="emergency_contact_phone">
                                <input id="emergency_contact_phone" wire:model="emergency_contact_phone" class="ignis-input" placeholder="(00) 00000-0000">
                            </x-field>
                        </div>
                    </section>

                    {{-- Termos --}}
                    <section class="ignis-card space-y-4">
                        <h2 class="text-lg font-bold text-ignis-gold">Termos de Autorização</h2>
                        @foreach ([
                            ['name' => 'term_truth', 'label' => 'Declaro que todas as informações prestadas são verdadeiras.'],
                            ['name' => 'term_participation', 'label' => 'Autorizo a participação no acampamento.'],
                            ['name' => 'term_emergency', 'label' => 'Autorizo atendimento de emergência, caso necessário.'],
                            ['name' => 'term_image', 'label' => 'Autorizo o uso de imagem em fotos e vídeos do evento.'],
                            ['name' => 'term_rules', 'label' => 'Comprometo-me a cumprir todas as normas do acampamento.'],
                        ] as $term)
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input
                                    type="checkbox"
                                    wire:model="{{ $term['name'] }}"
                                    class="mt-0.5 w-5 h-5 rounded border-slate-600 bg-ignis-navy-light accent-purple-500 cursor-pointer flex-shrink-0"
                                >
                                <span class="text-sm text-slate-300 group-hover:text-white transition-colors">{{ $term['label'] }}</span>
                            </label>
                            @error($term['name']) <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                        @endforeach
                    </section>

                    @if ($formError)
                        <div class="flex items-center gap-2 text-red-400 bg-red-500/10 border border-red-500/20 rounded-xl p-4">
                            <x-icon name="alert-circle" class="w-5 h-5 flex-shrink-0" />
                            <span class="text-sm">{{ $formError }}</span>
                        </div>
                    @endif

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="submit"
                        :disabled="age !== null && age < {{ $minAge }}"
                        class="ignis-btn-primary"
                    >
                        <span wire:loading.remove wire:target="submit">Continuar para Pagamento →</span>
                        <span wire:loading wire:target="submit" class="flex items-center gap-2">
                            <x-icon name="loader-2" class="w-5 h-5" /> Enviando...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>

@if (config('services.recaptcha.site_key'))
    @push('head-scripts')
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @endpush
@endif
