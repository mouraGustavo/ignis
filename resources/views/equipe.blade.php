<x-layouts.guest title="Equipe Organizadora">
    <div class="min-h-screen bg-ignis-navy-dark text-white">
        <section class="py-20 px-4 bg-ignis-navy border-b border-ignis-gold/10">
            <div class="max-w-4xl mx-auto text-center">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <x-icon name="flame" class="w-6 h-6 text-ignis-gold" />
                    <span class="font-bold text-white tracking-widest">IGNIS</span>
                </div>
                <h1 class="text-3xl md:text-5xl font-black mb-3">Equipe Organizadora</h1>
                <p class="text-white/60 max-w-2xl mx-auto">
                    Conheça quem torna o Acampamento Ignis possível — coordenação e líderes da Paróquia São Pedro Apóstolo.
                </p>
            </div>
        </section>

        <section class="py-16 px-4">
            <div class="max-w-5xl mx-auto">
                @if ($members->isEmpty())
                    <div class="text-center py-20 text-slate-500">Em breve divulgaremos nossa equipe!</div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        @foreach ($members as $member)
                            <div class="bg-ignis-navy border border-slate-800 rounded-2xl overflow-hidden group">
                                @if ($member->photo_url)
                                    <div class="aspect-square overflow-hidden">
                                        <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                @else
                                    <div class="aspect-square bg-ignis-navy-light flex items-center justify-center">
                                        <span class="text-5xl font-black text-ignis-gold/30">{{ mb_substr($member->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div class="p-5">
                                    <h3 class="font-bold text-white text-lg">{{ $member->name }}</h3>
                                    <p class="text-ignis-gold text-sm mb-2">{{ $member->role }}</p>
                                    @if ($member->bio)
                                        <p class="text-white/60 text-sm leading-relaxed">{{ $member->bio }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <div class="text-center pb-16">
            <a href="{{ route('home') }}" class="text-ignis-gold hover:text-ignis-gold-light transition-colors text-sm">
                ← Voltar ao início
            </a>
        </div>
    </div>
</x-layouts.guest>
