<div class="bg-ignis-navy-light/40 backdrop-blur-sm border border-ignis-gold/20 rounded-2xl p-6 text-white">
    <div class="flex justify-between items-center mb-3">
        <span class="text-sm font-medium uppercase tracking-wider opacity-80">Vagas</span>
        <span class="text-sm font-bold {{ $full ? 'text-red-300' : 'text-green-300' }}">
            {{ $full ? 'ESGOTADAS' : "{$remaining} restantes" }}
        </span>
    </div>
    <div class="w-full bg-white/20 rounded-full h-3 mb-3">
        <div
            class="h-3 rounded-full transition-all duration-700 {{ $full ? 'bg-red-400' : ($percent > 80 ? 'bg-amber-400' : 'bg-green-400') }}"
            style="width: {{ $percent }}%"
        ></div>
    </div>
    <div class="flex justify-between text-xs opacity-70">
        <span>{{ $confirmed }} confirmados</span>
        <span>{{ $maxSpots }} vagas no total</span>
    </div>
</div>
