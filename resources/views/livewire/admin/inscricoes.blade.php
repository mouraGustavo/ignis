<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
            <x-icon name="chevron-left" class="w-5 h-5" />
        </a>
        <div>
            <h1 class="text-2xl font-black">Inscrições</h1>
            <p class="text-slate-500 text-sm">{{ $confirmedCount }}/{{ $maxSpots }} vagas confirmadas</p>
        </div>
        <div class="ml-auto flex gap-3">
            <a href="{{ route('admin.reports.export-csv') }}" class="flex items-center gap-2 text-sm bg-ignis-navy-light hover:bg-slate-700 border border-slate-700 px-4 py-2 rounded-xl transition-colors">
                <x-icon name="download" class="w-4 h-4" /> Exportar CSV
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <x-icon name="search" class="w-4 h-4 absolute left-3 top-3 text-slate-500" />
            <input
                wire:model.live.debounce.400ms="search"
                placeholder="Buscar por nome, e-mail ou telefone..."
                class="w-full bg-ignis-navy border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500"
            >
        </div>
        <select wire:model.live="filterStatus" class="bg-ignis-navy border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-purple-500">
            <option value="all">Todos os status</option>
            <option value="{{ \App\Models\StatusInscricao::PENDENTE }}">Pendente</option>
            <option value="{{ \App\Models\StatusInscricao::AGUARDANDO_PAGAMENTO }}">Aguardando pagamento</option>
            <option value="{{ \App\Models\StatusInscricao::CONFIRMADA }}">Confirmado</option>
            <option value="{{ \App\Models\StatusInscricao::LISTA_ESPERA }}">Lista de espera</option>
            <option value="{{ \App\Models\StatusInscricao::CANCELADA }}">Cancelado</option>
            <option value="{{ \App\Models\StatusInscricao::RECUSADA }}">Recusado</option>
        </select>
    </div>

    <div class="bg-ignis-navy border border-slate-800 rounded-2xl overflow-hidden" wire:loading.class="opacity-60" wire:target="search,filterStatus,previousPage,nextPage">
        @if ($this->registrations->isEmpty())
            <div class="text-center py-16 text-slate-500">Nenhuma inscrição encontrada.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-800">
                            <th class="text-left text-xs font-medium text-slate-500 px-4 py-3 uppercase tracking-wider">Nome</th>
                            <th class="text-left text-xs font-medium text-slate-500 px-4 py-3 uppercase tracking-wider hidden md:table-cell">Contato</th>
                            <th class="text-left text-xs font-medium text-slate-500 px-4 py-3 uppercase tracking-wider hidden sm:table-cell">Pagamento</th>
                            <th class="text-left text-xs font-medium text-slate-500 px-4 py-3 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach ($this->registrations as $reg)
                            <tr class="hover:bg-ignis-navy-light/50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-sm">{{ $reg->inscrito->nome }}</div>
                                    <div class="text-xs text-slate-500">{{ $reg->inscrito->idade }} anos · {{ $reg->inscrito->tamanhoCamisa?->tamanho }}</div>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <div class="text-sm text-slate-300">{{ $reg->inscrito->contato->telefone ?? '' }}</div>
                                    <div class="text-xs text-slate-500">{{ $reg->inscrito->contato->email ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <span class="text-xs text-slate-400 capitalize">{{ $reg->metodo_pagamento === 'pix' ? 'PIX' : ($reg->metodo_pagamento === 'credit_card' ? 'Cartão' : '—') }}</span>
                                    @if ($reg->payment?->card_mask)
                                        <span class="block text-xs text-slate-500">{{ $reg->payment->card_mask }}</span>
                                    @endif
                                    @if ($reg->comprovante_pagamento_url)
                                        <a href="{{ route('admin.files.show', ['path' => $reg->comprovante_pagamento_url]) }}" target="_blank" class="ml-2 text-xs text-ignis-gold hover:underline">ver</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$reg->status->nome" />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1 justify-end" wire:loading.class="opacity-50" wire:target="confirm({{ $reg->id }}),cancel({{ $reg->id }})">
                                        @if (in_array($reg->status->nome, [\App\Models\StatusInscricao::PENDENTE, \App\Models\StatusInscricao::AGUARDANDO_PAGAMENTO], true))
                                            <button wire:click="confirm({{ $reg->id }})" title="Confirmar" class="p-1.5 rounded-lg bg-green-500/10 hover:bg-green-500/20 text-green-400 transition-colors">
                                                <x-icon name="check" class="w-4 h-4" />
                                            </button>
                                            <button wire:click="cancel({{ $reg->id }})" title="Cancelar" wire:confirm="Cancelar esta inscrição?" class="p-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 transition-colors">
                                                <x-icon name="x" class="w-4 h-4" />
                                            </button>
                                        @endif
                                        <button wire:click="show({{ $reg->id }})" title="Ver detalhes" class="p-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 transition-colors">
                                            <x-icon name="eye" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between px-4 py-3 border-t border-slate-800 text-sm text-slate-400">
                <span>{{ $this->registrations->firstItem() }}–{{ $this->registrations->lastItem() }} de {{ $this->registrations->total() }}</span>
                <div class="flex gap-2">
                    <button wire:click="previousPage" @disabled($this->registrations->onFirstPage()) class="px-3 py-1.5 rounded-lg bg-ignis-navy-light border border-slate-700 disabled:opacity-40">Anterior</button>
                    <button wire:click="nextPage" @disabled(! $this->registrations->hasMorePages()) class="px-3 py-1.5 rounded-lg bg-ignis-navy-light border border-slate-700 disabled:opacity-40">Próxima</button>
                </div>
            </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    @if ($this->selected)
        <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 px-4" wire:click="closeModal">
            <div class="bg-ignis-navy border border-slate-800 rounded-2xl p-6 max-w-lg w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold">{{ $this->selected->inscrito->nome }}</h2>
                    <button wire:click="closeModal" class="text-slate-500 hover:text-white">
                        <x-icon name="x" class="w-5 h-5" />
                    </button>
                </div>
                <dl class="space-y-3 text-sm">
                    @foreach ([
                        ['Status', null],
                        ['E-mail', $this->selected->inscrito->contato->email ?? '—'],
                        ['Telefone', $this->selected->inscrito->contato->telefone ?? '—'],
                        ['Data de nascimento', $this->selected->inscrito->data_nascimento?->format('d/m/Y')],
                        ['Idade', $this->selected->inscrito->idade ? "{$this->selected->inscrito->idade} anos" : '—'],
                        ['Camiseta', $this->selected->inscrito->tamanhoCamisa?->tamanho],
                        ['Pagamento', $this->selected->metodo_pagamento === 'pix' ? 'PIX' : ($this->selected->metodo_pagamento === 'credit_card' ? "Cartão ({$this->selected->parcelas_pagamento}x)" : '—')],
                        ['Status pagamento', $this->selected->payment?->status?->label() ?? '—'],
                        ['ID cobrança Efí', $this->selected->payment?->transaction_id ?? '—'],
                        ['Cartão', $this->selected->payment?->card_mask ?? '—'],
                        ['Motivo recusa', $this->selected->payment?->failure_reason ?? '—'],
                        ['Responsável', $this->selected->inscrito->responsavel?->nome ?? '—'],
                        ['Restrições alimentares', $this->selected->inscrito->informacaoSaude?->restricoes_alimentares ?: '—'],
                        ['Alergias', $this->selected->inscrito->informacaoSaude?->alergias ?: '—'],
                        ['Medicamentos', $this->selected->inscrito->informacaoSaude?->medicamentos ?: '—'],
                        ['Condições de saúde', $this->selected->inscrito->informacaoSaude?->condicao_saude ?: '—'],
                        ['Emergência', $this->selected->inscrito->contato->nome_emergencia ? "{$this->selected->inscrito->contato->nome_emergencia} – {$this->selected->inscrito->contato->telefone_emergencia}" : '—'],
                    ] as [$label, $value])
                        <div class="flex gap-3">
                            <dt class="text-slate-500 w-36 flex-shrink-0">{{ $label }}</dt>
                            <dd class="text-white">
                                @if ($label === 'Status')
                                    <x-status-badge :status="$this->selected->status->nome" />
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>

                @if ($this->selected->inscrito->responsavel?->documento_url)
                    <div class="mt-3">
                        <a href="{{ route('admin.files.show', ['path' => $this->selected->inscrito->responsavel->documento_url]) }}" target="_blank" class="text-sm text-ignis-gold hover:underline">Ver documento do responsável</a>
                    </div>
                @endif
                @if ($this->selected->comprovante_pagamento_url)
                    <div class="mt-3">
                        <a href="{{ route('admin.files.show', ['path' => $this->selected->comprovante_pagamento_url]) }}" target="_blank" class="text-sm text-ignis-gold hover:underline">Ver comprovante de pagamento</a>
                    </div>
                @endif

                @if (in_array($this->selected->status->nome, [\App\Models\StatusInscricao::PENDENTE, \App\Models\StatusInscricao::AGUARDANDO_PAGAMENTO], true))
                    <div class="flex gap-3 mt-6">
                        <button wire:click="confirm({{ $this->selected->id }})" class="flex-1 py-2.5 bg-green-600 hover:bg-green-500 rounded-xl text-sm font-semibold transition-colors">
                            Confirmar
                        </button>
                        <button wire:click="cancel({{ $this->selected->id }})" wire:confirm="Cancelar esta inscrição?" class="flex-1 py-2.5 bg-red-600/30 hover:bg-red-600/50 border border-red-600/40 rounded-xl text-sm font-semibold text-red-400 transition-colors">
                            Cancelar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
