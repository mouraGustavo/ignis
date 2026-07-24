@php
$value = (float) ($settings['registration_value'] ?? '150');
@endphp
<div>
    @if ($done)
        <div class="min-h-screen bg-ignis-navy-dark flex items-center justify-center px-4">
            <div class="max-w-md w-full text-center">
                <div class="text-7xl mb-6">🎉</div>
                <h2 class="text-3xl font-black text-white mb-3">Inscrição enviada!</h2>
                <p class="text-slate-400 mb-6">
                    Sua inscrição está <strong class="text-amber-400">pendente</strong> de confirmação. Assim que o pagamento for validado, você receberá um e-mail de confirmação.
                </p>
                <div class="ignis-card !p-4 text-left text-sm text-slate-400 mb-8">
                    <p><span class="text-white font-medium">Nome:</span> {{ $eventoInscrito->inscrito->nome }}</p>
                    <p><span class="text-white font-medium">E-mail:</span> {{ $eventoInscrito->inscrito->contato->email }}</p>
                    <p><span class="text-white font-medium">Status:</span> <span class="text-amber-400">Pendente</span></p>
                </div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-ignis-gold hover:text-purple-300 transition-colors">
                    <x-icon name="chevron-left" class="w-4 h-4" /> Voltar ao início
                </a>
            </div>
        </div>
    @else
        <div class="min-h-screen bg-ignis-navy-dark text-white py-10 px-4">
            <div class="max-w-lg mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl font-black mb-1">Pagamento</h1>
                    <p class="text-slate-400">Olá, <strong class="text-white">{{ $eventoInscrito->inscrito->nome }}</strong>! Finalize seu pagamento.</p>
                </div>

                <div class="bg-gradient-to-r from-ignis-gold/20 to-ignis-gold-dark/20 border border-purple-500/30 rounded-2xl p-5 mb-6 flex items-center justify-between">
                    <span class="text-slate-300">Valor da inscrição</span>
                    <span class="text-3xl font-black text-white">R$ {{ number_format($value, 2, ',', '.') }}</span>
                </div>

                {{-- Metodo --}}
                <div class="flex gap-3 mb-6">
                    <button type="button" wire:click="$set('method', 'pix')" class="flex-1 py-3 rounded-xl border font-semibold text-sm transition-all {{ $method === 'pix' ? 'bg-ignis-gold border-purple-500 text-white' : 'bg-ignis-navy-light border-slate-700 text-slate-400 hover:border-slate-500' }}">
                        PIX
                    </button>
                    <button type="button" wire:click="$set('method', 'credit_card')" class="flex-1 py-3 rounded-xl border font-semibold text-sm transition-all {{ $method === 'credit_card' ? 'bg-ignis-gold border-purple-500 text-white' : 'bg-ignis-navy-light border-slate-700 text-slate-400 hover:border-slate-500' }}">
                        Cartão de Crédito
                    </button>
                </div>

                <form wire:submit="submit" class="space-y-5">
                    @if ($method === 'pix')
                        <div class="ignis-card space-y-5">
                            <h3 class="font-bold text-ignis-gold">Pague via PIX</h3>
                            @if (!empty($settings['pix_key']))
                                <div>
                                    <p class="text-sm text-slate-400 mb-2">
                                        Chave PIX @if(!empty($settings['pix_name']))({{ $settings['pix_name'] }})@endif:
                                    </p>
                                    <div
                                        x-data="{ copied: false }"
                                        class="flex items-center gap-2 bg-ignis-navy-light border border-slate-700 rounded-xl px-4 py-3"
                                    >
                                        <span class="flex-1 font-mono text-sm text-white break-all">{{ $settings['pix_key'] }}</span>
                                        <button
                                            type="button"
                                            @click="navigator.clipboard.writeText(@js($settings['pix_key'])); copied = true; setTimeout(() => copied = false, 2000)"
                                            class="text-slate-400 hover:text-ignis-gold transition-colors flex-shrink-0"
                                        >
                                            <x-icon name="check" class="w-5 h-5 text-green-400" x-show="copied" x-cloak />
                                            <x-icon name="copy" class="w-5 h-5" x-show="!copied" />
                                        </button>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-2">Valor: R$ {{ number_format($value, 2, ',', '.') }}</p>
                                    @if (!empty($settings['pix_qrcode_url']))
                                        <div class="mt-4 flex justify-center">
                                            <img src="{{ $settings['pix_qrcode_url'] }}" alt="QR Code do PIX" class="w-48 h-48 object-contain bg-white rounded-xl p-2">
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">Comprovante de pagamento *</label>
                                    <x-file-dropzone model="proofFile" accept="image/*,application/pdf" hint="Captura de tela ou PDF do comprovante" :file="$proofFile" />
                                </div>
                            @else
                                <p class="text-slate-400 text-sm">Chave PIX não configurada. Entre em contato com a organização.</p>
                            @endif
                        </div>
                    @endif

                    @if ($method === 'credit_card')
                        <div class="ignis-card space-y-4">
                            <h3 class="font-bold text-ignis-gold">Cartão de Crédito</h3>
                            <div class="bg-amber-900/20 border border-amber-700/40 rounded-xl p-4 text-sm text-amber-300">
                                <x-icon name="alert-circle" class="w-4 h-4 inline mr-2" />
                                O pagamento por cartão será processado manualmente pela organização. Selecione o número de parcelas e aguarde o contato da equipe.
                            </div>
                            <x-formulario-cartao-credito />
                        </div>
                    @endif

                    @error('proofFile') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
                    @error('installments') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror

                    @if ($formError)
                        <div class="flex items-center gap-2 text-red-400 bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-sm">
                            <x-icon name="alert-circle" class="w-4 h-4 flex-shrink-0" /> {{ $formError }}
                        </div>
                    @endif

                    <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="ignis-btn-primary">
                        <span wire:loading.remove wire:target="submit">Confirmar Inscrição →</span>
                        <span wire:loading wire:target="submit" class="flex items-center gap-2">
                            <x-icon name="loader-2" class="w-5 h-5" /> Enviando...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
