<x-layouts.admin title="Painel">
    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="text-3xl font-black">Painel Administrativo</h1>
                <p class="text-slate-400 mt-1">Gerencie as inscrições e configurações do acampamento</p>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            @foreach ([
                ['label' => 'Total', 'value' => $stats['total'], 'color' => 'text-white'],
                ['label' => 'Confirmados', 'value' => $stats['confirmed'], 'color' => 'text-green-400'],
                ['label' => 'Pendentes', 'value' => $stats['pending'], 'color' => 'text-amber-400'],
                ['label' => 'Cancelados', 'value' => $stats['cancelled'], 'color' => 'text-red-400'],
            ] as $s)
                <div class="ignis-card !p-5 text-center">
                    <div class="text-3xl font-black {{ $s['color'] }}">{{ $s['value'] }}</div>
                    <div class="text-sm text-slate-500 mt-1">{{ $s['label'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- Navigation --}}
        <div class="grid md:grid-cols-3 gap-4">
            @foreach ([
                ['route' => 'admin.inscricoes', 'icon' => 'users', 'label' => 'Inscrições', 'desc' => 'Ver, confirmar e gerenciar participantes'],
                ['route' => 'admin.configuracoes', 'icon' => 'settings', 'label' => 'Configurações', 'desc' => 'Título, vagas, PIX, data, localização'],
                ['route' => 'admin.midias', 'icon' => 'image', 'label' => 'Mídias', 'desc' => 'Fotos e vídeos exibidos no site'],
                ['route' => 'admin.relatorios', 'icon' => 'bar-chart-3', 'label' => 'Relatórios', 'desc' => 'Gráficos de ocupação e financeiro'],
                ['route' => 'admin.equipe', 'icon' => 'users', 'label' => 'Equipe', 'desc' => 'Coordenação e líderes da paróquia'],
            ] as $card)
                <a href="{{ route($card['route']) }}" class="bg-gradient-to-br from-ignis-gold/20 to-ignis-gold-dark/10 border border-ignis-gold/30 rounded-2xl p-6 hover:scale-[1.02] transition-transform flex flex-col justify-between min-h-36 group">
                    <div class="text-ignis-gold mb-4">
                        <x-icon :name="$card['icon']" class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="font-bold text-lg text-white mb-1">{{ $card['label'] }}</div>
                        <div class="text-sm text-slate-400 mb-3">{{ $card['desc'] }}</div>
                        <div class="flex items-center gap-1 text-xs text-slate-500 group-hover:text-slate-300 transition-colors">
                            Acessar <x-icon name="chevron-right" class="w-3 h-3" />
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
