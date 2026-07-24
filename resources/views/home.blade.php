<x-layouts.guest :title="$settings['site_title']">
    <div class="min-h-screen bg-ignis-navy-dark text-white font-body">
        {{-- Hero --}}
        <section class="relative min-h-screen flex flex-col items-center justify-center px-4 py-16 overflow-hidden bg-ignis-navy">
            <div class="absolute top-10 left-5 w-72 h-72 bg-ignis-gold/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-5 w-96 h-96 bg-ignis-gold/5 rounded-full blur-3xl"></div>

            <div class="relative z-10 max-w-4xl mx-auto text-center">
                <div class="w-28 h-28 md:w-36 md:h-36 rounded-full bg-white p-3 mx-auto mb-8 shadow-2xl ring-4 ring-ignis-gold/20">
                    <img src="{{ $logoUrl }}" alt="Acampamento Ignis" class="w-full h-full object-contain">
                </div>

                <div class="mb-1">
                    <span class="text-5xl md:text-7xl font-black text-ignis-gold">5º</span>
                </div>
                <h1 class="text-3xl md:text-5xl font-black tracking-tight text-white leading-tight mb-2">
                    ACAMPAMENTO DE JOVENS
                </h1>
                <div class="mb-8">
                    <x-orbital-ring>
                        <span class="text-4xl md:text-6xl font-black text-ignis-gold">IGNIS</span>
                    </x-orbital-ring>
                </div>

                <div class="flex flex-wrap justify-center gap-6 mb-8 text-white/70 text-sm">
                    @if ($settings['event_date'])
                        <div class="flex items-center gap-2">
                            <x-icon name="calendar" class="w-4 h-4 text-ignis-gold" />
                            {{ \Illuminate\Support\Carbon::parse($settings['event_date'])->translatedFormat('d \d\e F \d\e Y') }}
                        </div>
                    @endif
                    @if ($settings['event_location'])
                        <div class="flex items-center gap-2">
                            <x-icon name="map-pin" class="w-4 h-4 text-ignis-gold" /> {{ $settings['event_location'] }}
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <x-icon name="users" class="w-4 h-4 text-ignis-gold" /> {{ (int) $settings['max_spots'] }} vagas
                    </div>
                </div>

                <div class="max-w-sm mx-auto mb-8">
                    <x-vagas-counter :max-spots="(int) $settings['max_spots']" />
                </div>

                @if ($vagasEsgotadas)
                    <div class="inline-block bg-white/10 border border-white/20 text-white/60 rounded-2xl px-8 py-4 text-lg">
                        Inscrições esgotadas — limite de vagas atingido
                    </div>
                @elseif ($settings['registration_open'] === 'true')
                    <a href="{{ route('inscricao') }}" class="inline-flex items-center gap-2 bg-ignis-gold hover:bg-ignis-gold-dark text-ignis-navy-dark font-bold text-lg px-10 py-4 rounded-2xl transition-all duration-200 shadow-lg shadow-ignis-gold/20 hover:scale-105">
                        Fazer Inscrição <x-icon name="chevron-right" class="w-5 h-5" />
                    </a>
                @else
                    <div class="inline-block bg-white/10 border border-white/20 text-white/60 rounded-2xl px-8 py-4 text-lg">
                        Inscrições temporariamente fechadas
                    </div>
                @endif

                <div class="mt-4">
                    <a href="{{ route('minha-inscricao') }}" class="text-sm text-white/60 hover:text-ignis-gold transition-colors underline underline-offset-4">
                        Já se inscreveu? Acessar minha inscrição
                    </a>
                </div>
            </div>
        </section>

        {{-- About --}}
        <section class="py-24 px-4 bg-ignis-navy-dark relative">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-xl md:text-2xl font-bold text-white/80 mb-8 tracking-wide">O QUE É O ACAMPAMENTO</h2>
                <div class="mb-10">
                    <x-orbital-ring>
                        <span class="text-4xl md:text-6xl font-black text-ignis-gold">IGNIS?</span>
                    </x-orbital-ring>
                </div>
                <p class="text-lg md:text-xl text-white/70 leading-relaxed max-w-2xl mx-auto font-medium">
                    UM ENCONTRO DE JOVENS PARA VIVER DIAS INTENSOS DE FÉ, ORAÇÃO, AMIZADE E MUITA ALEGRIA!
                </p>
                <div class="flex items-center justify-center gap-2 mt-10">
                    <x-icon name="flame" class="w-6 h-6 text-ignis-gold" />
                    <span class="font-bold text-white tracking-widest">IGNIS</span>
                </div>
            </div>
        </section>

        {{-- Quote --}}
        <section class="py-20 px-4 bg-ignis-navy border-y border-ignis-gold/10">
            <div class="max-w-3xl mx-auto text-center">
                <p class="text-2xl md:text-4xl font-bold text-white leading-tight mb-4">
                    "JOVENS, não tenhais MEDO DE SEREM SANTOS"
                </p>
                <p class="text-sm text-ignis-gold uppercase tracking-wider">— São João Paulo II</p>
            </div>
        </section>

        {{-- Parish --}}
        <section class="py-24 px-4 bg-ignis-navy-dark">
            <div class="max-w-5xl mx-auto">
                <div class="grid md:grid-cols-2 gap-10 items-center">
                    <div class="text-center md:text-right">
                        <img src="{{ $brasaoUrl }}" alt="Brasão Paróquia São Pedro Apóstolo" class="w-40 mx-auto md:ml-auto mb-6">
                        <h2 class="text-2xl font-bold text-white mb-3">Paróquia São Pedro Apóstolo</h2>
                        <p class="text-white/60 leading-relaxed">
                            O Acampamento Ignis é uma realização da Paróquia São Pedro Apóstolo, reunindo os jovens da nossa comunidade para viver uma experiência única de fé e comunhão.
                        </p>
                    </div>
                    <div>
                        <img src="{{ $churchUrl }}" alt="Paróquia São Pedro Apóstolo" class="rounded-2xl w-full shadow-xl">
                    </div>
                </div>
            </div>
        </section>

        {{-- Gallery --}}
        @if (count($galleryPhotos) > 0)
            <section class="py-24 px-4 bg-ignis-navy">
                <div class="max-w-6xl mx-auto">
                    <h2 class="text-3xl font-black text-center text-white mb-3">Galeria</h2>
                    <p class="text-center text-white/50 mb-12">Momentos das edições anteriores</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($galleryPhotos as $url)
                            <div class="rounded-xl overflow-hidden aspect-square bg-ignis-navy-light group">
                                <img src="{{ $url }}" alt="Foto {{ $loop->iteration }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Footer --}}
        <footer class="bg-ignis-navy border-t border-ignis-gold/10 py-10 px-4">
            <div class="max-w-4xl mx-auto text-center">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <x-icon name="flame" class="w-6 h-6 text-ignis-gold" />
                    <span class="font-bold text-white text-lg tracking-widest">IGNIS</span>
                </div>
                <p class="text-white/60 text-sm mb-1">5º Acampamento de Jovens Ignis &copy; {{ now()->year }}</p>
                <p class="text-white/40 text-sm mb-6">Paróquia São Pedro Apóstolo</p>
                <a href="{{ $settings['instagram_url'] ?: 'https://www.instagram.com/ignis_acamp' }}" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 text-ignis-gold hover:text-ignis-gold-light transition-colors">
                    <x-icon name="instagram" class="w-5 h-5" /> @ignis_acamp
                </a>
                <div class="mt-8">
                    <a href="{{ route('login') }}" class="text-xs text-white/30 hover:text-white/60 transition-colors">Acesso Administrativo</a>
                </div>
            </div>
        </footer>
    </div>
</x-layouts.guest>
