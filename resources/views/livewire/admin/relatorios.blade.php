<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
            <x-icon name="chevron-left" class="w-5 h-5" />
        </a>
        <div>
            <h1 class="text-2xl font-black">Relatórios</h1>
            <p class="text-slate-500 text-sm">Visão geral das inscrições e pagamentos</p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="ignis-card !p-5">
            <div class="text-ignis-gold mb-2"><x-icon name="users" class="w-5 h-5" /></div>
            <div class="text-2xl font-black">{{ $totalInscricoes }}</div>
            <div class="text-sm text-slate-500">Total de inscrições</div>
        </div>
        <div class="ignis-card !p-5">
            <div class="text-ignis-gold mb-2"><x-icon name="trending-up" class="w-5 h-5" /></div>
            <div class="text-2xl font-black">{{ $occupancyRate }}%</div>
            <div class="text-sm text-slate-500">Taxa de ocupação</div>
            <div class="text-xs text-slate-600 mt-1">{{ $confirmedCount }}/{{ $maxSpots }} vagas</div>
        </div>
        <div class="ignis-card !p-5">
            <div class="text-ignis-gold mb-2"><x-icon name="dollar-sign" class="w-5 h-5" /></div>
            <div class="text-2xl font-black">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</div>
            <div class="text-sm text-slate-500">Receita confirmada</div>
        </div>
        <div class="ignis-card !p-5">
            <div class="text-ignis-gold mb-2"><x-icon name="dollar-sign" class="w-5 h-5" /></div>
            <div class="text-2xl font-black">R$ {{ number_format($pendingRevenue, 2, ',', '.') }}</div>
            <div class="text-sm text-slate-500">Receita pendente</div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div class="ignis-card">
            <h2 class="font-bold text-ignis-gold mb-4">Status das Inscrições</h2>
            <div class="h-[250px]"><canvas id="chart-status"></canvas></div>
        </div>
        <div class="ignis-card">
            <h2 class="font-bold text-ignis-gold mb-4">Status Financeiro</h2>
            <div class="h-[250px]"><canvas id="chart-financial"></canvas></div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="ignis-card">
            <h2 class="font-bold text-ignis-gold mb-4">Faixas Etárias</h2>
            <div class="h-[250px]"><canvas id="chart-age"></canvas></div>
        </div>
        <div class="ignis-card">
            <h2 class="font-bold text-ignis-gold mb-4">Tamanhos de Camiseta</h2>
            <div class="h-[250px]"><canvas id="chart-shirt"></canvas></div>
        </div>
    </div>

    @script
    <script>
        const tooltipStyle = {
            backgroundColor: '#1a3666',
            borderColor: '#334155',
            borderWidth: 1,
            titleColor: '#fff',
            bodyColor: '#fff',
            padding: 10,
        };
        const legendStyle = { labels: { color: '#e2e8f0', font: { size: 12 } } };
        const gridStyle = { color: '#334155' };
        const tickStyle = { color: '#94a3b8', font: { size: 12 } };

        new Chart(document.getElementById('chart-status'), {
            type: 'pie',
            data: {
                labels: @json($statusData->pluck('name')),
                datasets: [{ data: @json($statusData->pluck('value')), backgroundColor: @json($statusData->pluck('color')) }],
            },
            options: { plugins: { legend: legendStyle, tooltip: tooltipStyle } },
        });

        new Chart(document.getElementById('chart-financial'), {
            type: 'pie',
            data: {
                labels: @json($financialData->pluck('name')),
                datasets: [{ data: @json($financialData->pluck('value')), backgroundColor: @json($financialData->pluck('color')) }],
            },
            options: { plugins: { legend: legendStyle, tooltip: tooltipStyle } },
        });

        new Chart(document.getElementById('chart-age'), {
            type: 'bar',
            data: {
                labels: @json($ageData->pluck('name')),
                datasets: [
                    { label: 'Total', data: @json($ageData->pluck('Total')), backgroundColor: '#ffcc00', borderRadius: 4 },
                    { label: 'Confirmados', data: @json($ageData->pluck('Confirmados')), backgroundColor: '#22c55e', borderRadius: 4 },
                ],
            },
            options: {
                plugins: { legend: legendStyle, tooltip: tooltipStyle },
                scales: {
                    x: { grid: gridStyle, ticks: tickStyle },
                    y: { grid: gridStyle, ticks: { ...tickStyle, precision: 0 } },
                },
            },
        });

        new Chart(document.getElementById('chart-shirt'), {
            type: 'bar',
            data: {
                labels: @json($shirtData->pluck('name')),
                datasets: [{ label: 'Quantidade', data: @json($shirtData->pluck('Quantidade')), backgroundColor: '#ffcc00', borderRadius: 4 }],
            },
            options: {
                plugins: { legend: { display: false }, tooltip: tooltipStyle },
                scales: {
                    x: { grid: gridStyle, ticks: tickStyle },
                    y: { grid: gridStyle, ticks: { ...tickStyle, precision: 0 } },
                },
            },
        });
    </script>
    @endscript
</div>
