<div class="min-h-screen bg-ignis-navy-dark text-white py-10 px-4">
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-slate-400 hover:text-white mb-8 text-sm transition-colors">
            <x-icon name="chevron-left" class="w-4 h-4" /> Voltar ao início
        </a>

        <div class="mb-8">
            <h1 class="text-3xl font-black mb-2">Minha Inscrição</h1>
            <p class="text-slate-400">Digite o e-mail usado na inscrição para ver o status.</p>
        </div>

        <form wire:submit="search" class="flex gap-3 mb-8">
            <div class="relative flex-1">
                <x-icon name="search" class="w-5 h-5 absolute left-4 top-3.5 text-slate-500" />
                <input
                    type="email"
                    wire:model="email"
                    required
                    placeholder="seu@email.com"
                    class="w-full bg-ignis-navy border border-slate-700 rounded-xl pl-12 pr-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-ignis-gold transition-colors text-sm"
                >
            </div>
            <button type="submit" wire:loading.attr="disabled" wire:target="search" class="bg-ignis-gold hover:bg-ignis-gold-dark text-ignis-navy-dark font-bold px-6 py-3 rounded-xl text-sm transition-colors disabled:opacity-50 flex items-center gap-2">
                <span wire:loading.remove wire:target="search"><x-icon name="search" class="w-4 h-4" /></span>
                <span wire:loading wire:target="search"><x-icon name="loader-2" class="w-4 h-4" /></span>
                Buscar
            </button>
        </form>
        @error('email') <p class="text-red-400 text-xs -mt-6 mb-6">{{ $message }}</p> @enderror

        @if ($searched && $results && $results->isEmpty())
            <div class="ignis-card !p-8 text-center">
                <x-icon name="alert-circle" class="w-10 h-10 text-slate-500 mx-auto mb-3" />
                <p class="text-slate-400">Nenhuma inscrição encontrada com este e-mail.</p>
                <p class="text-slate-600 text-sm mt-2">Verifique se digitou o e-mail correto ou entre em contato com a organização.</p>
            </div>
        @endif

        @if ($searched && $results && $results->isNotEmpty())
            <div class="space-y-4">
                @foreach ($results as $reg)
                    @php
                        $statusNome = $reg->status->nome;
                        $category = match (true) {
                            $statusNome === \App\Models\StatusInscricao::CONFIRMADA => 'confirmed',
                            in_array($statusNome, [\App\Models\StatusInscricao::CANCELADA, \App\Models\StatusInscricao::RECUSADA], true) => 'cancelled',
                            default => 'pending',
                        };
                    @endphp
                    <div class="ignis-card">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="font-bold text-lg">{{ $reg->inscrito->nome }}</h2>
                                <p class="text-sm text-slate-500">{{ $reg->inscrito->tamanhoCamisa?->tamanho }} · {{ $reg->inscrito->idade }} anos</p>
                            </div>
                            <x-status-badge :status="$statusNome" />
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-slate-500 mb-1">Pagamento</p>
                                <p class="text-white">{{ $reg->metodo_pagamento === 'pix' ? 'PIX' : ($reg->metodo_pagamento === 'credit_card' ? 'Cartão de crédito' : 'Não informado') }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 mb-1">Comprovante</p>
                                <p class="text-white">{{ $reg->comprovante_pagamento_url ? 'Enviado' : 'Não enviado' }}</p>
                            </div>
                        </div>

                        @if ($category === 'pending')
                            <div class="mt-4 bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 text-sm text-amber-300">
                                <x-icon name="clock" class="w-4 h-4 inline mr-2" />
                                Sua inscrição está aguardando confirmação do pagamento. Assim que for validada pela organização, você receberá um e-mail de confirmação.
                            </div>
                        @elseif ($category === 'confirmed')
                            <div class="mt-4 bg-green-500/10 border border-green-500/20 rounded-xl p-4 text-sm text-green-300">
                                <x-icon name="check" class="w-4 h-4 inline mr-2" />
                                Sua inscrição está confirmada! Nos vemos no acampamento. 🎉
                            </div>
                        @else
                            <div class="mt-4 bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-sm text-red-300">
                                <x-icon name="x" class="w-4 h-4 inline mr-2" />
                                Sua inscrição foi cancelada. Entre em contato com a organização para mais informações.
                            </div>
                        @endif

                        @if ($editingId !== $reg->id)
                            @if ($this->canEdit($reg))
                                <button type="button" wire:click="edit({{ $reg->id }})" class="mt-4 inline-flex items-center gap-2 text-sm text-ignis-gold hover:text-ignis-gold-light transition-colors">
                                    <x-icon name="pencil" class="w-4 h-4" /> Alterar meus dados
                                </button>
                            @elseif ($category !== 'cancelled' && $reg->evento?->data_limite_inscricao)
                                <p class="mt-4 text-xs text-slate-500">
                                    O prazo para alterar os dados desta inscrição encerrou em {{ $reg->evento->data_limite_inscricao->translatedFormat('d \d\e F \d\e Y') }}.
                                </p>
                            @endif
                        @else
                            <form wire:submit="saveEdit" class="mt-6 pt-6 border-t border-slate-800 space-y-5">
                                <x-field label="Nome completo *" for="editFullName">
                                    <input id="editFullName" wire:model="editFullName" required class="ignis-input">
                                    @error('editFullName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </x-field>

                                <div class="grid grid-cols-2 gap-4">
                                    <x-field label="Telefone *" for="editPhone">
                                        <input id="editPhone" wire:model="editPhone" required class="ignis-input">
                                        @error('editPhone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                    </x-field>
                                    <x-field label="Data de nascimento *" for="editBirthDate">
                                        <input id="editBirthDate" type="date" wire:model.live="editBirthDate" required class="ignis-input">
                                        @error('editBirthDate') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                    </x-field>
                                </div>

                                <x-field label="Tamanho da camiseta *" for="editShirtSize">
                                    <select id="editShirtSize" wire:model="editShirtSize" required class="ignis-input">
                                        <option value="">Selecione</option>
                                        @foreach (\App\Livewire\Inscricao::SHIRT_SIZES as $size)
                                            <option value="{{ $size }}">{{ $size }}</option>
                                        @endforeach
                                    </select>
                                    @error('editShirtSize') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </x-field>

                                @if ($editAge !== null && $editAge < 18)
                                    <div class="bg-amber-900/20 border border-amber-700/40 rounded-2xl p-5 space-y-4">
                                        <h3 class="text-sm font-bold text-amber-400">Responsável (menor de 18 anos)</h3>
                                        <x-field label="Nome do responsável *" for="editGuardianName">
                                            <input id="editGuardianName" wire:model="editGuardianName" required class="ignis-input">
                                            @error('editGuardianName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                        </x-field>
                                        <x-field label="Telefone do responsável" for="editGuardianPhone">
                                            <input id="editGuardianPhone" wire:model="editGuardianPhone" class="ignis-input">
                                        </x-field>
                                        <x-field label="Documento do responsável (foto + assinatura) {{ $existingGuardianDocumentUrl ? '' : '*' }}">
                                            @if ($existingGuardianDocumentUrl && ! $editGuardianDocument)
                                                <p class="text-xs text-green-400 mb-2">✓ Documento já enviado — envie um novo arquivo apenas se quiser substituí-lo.</p>
                                            @endif
                                            <x-file-dropzone model="editGuardianDocument" accept="image/*,application/pdf" hint="Foto do documento com assinatura — JPG, PNG ou PDF" :file="$editGuardianDocument" accent="gold" />
                                            @error('editGuardianDocument') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                        </x-field>
                                    </div>
                                @endif

                                <x-field label="Restrições alimentares" for="editFoodRestrictions">
                                    <textarea id="editFoodRestrictions" wire:model="editFoodRestrictions" rows="2" class="ignis-input"></textarea>
                                </x-field>
                                <x-field label="Alergias" for="editAllergies">
                                    <textarea id="editAllergies" wire:model="editAllergies" rows="2" class="ignis-input"></textarea>
                                </x-field>
                                <x-field label="Medicamentos de uso contínuo" for="editMedications">
                                    <textarea id="editMedications" wire:model="editMedications" rows="2" class="ignis-input"></textarea>
                                </x-field>
                                <x-field label="Condições de saúde" for="editHealthConditions">
                                    <textarea id="editHealthConditions" wire:model="editHealthConditions" rows="2" class="ignis-input"></textarea>
                                </x-field>

                                <div class="grid grid-cols-2 gap-4">
                                    <x-field label="Contato de emergência" for="editEmergencyContactName">
                                        <input id="editEmergencyContactName" wire:model="editEmergencyContactName" class="ignis-input">
                                    </x-field>
                                    <x-field label="Telefone de emergência" for="editEmergencyContactPhone">
                                        <input id="editEmergencyContactPhone" wire:model="editEmergencyContactPhone" class="ignis-input">
                                    </x-field>
                                </div>

                                <div class="flex gap-3 pt-2">
                                    <button type="submit" wire:loading.attr="disabled" wire:target="saveEdit" class="bg-ignis-gold hover:bg-ignis-gold-dark text-ignis-navy-dark font-bold px-6 py-3 rounded-xl text-sm transition-colors disabled:opacity-50 flex items-center gap-2">
                                        <span wire:loading wire:target="saveEdit"><x-icon name="loader-2" class="w-4 h-4" /></span>
                                        Salvar alterações
                                    </button>
                                    <button type="button" wire:click="cancelEdit" class="text-sm text-slate-400 hover:text-white transition-colors px-4">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
