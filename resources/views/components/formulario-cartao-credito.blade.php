<div
    id="credit-card-form-root"
    class="ignis-card space-y-4"
    x-data="creditCardForm({
        valueCents: @js($valueCents),
        payeeCode: @js($payeeCode),
        environment: @js($environment),
        maxInstallments: @js($maxInstallments),
    })"
    x-init="init()"
>
    <template x-if="scriptBlocked">
        <div class="bg-red-900/20 border border-red-700/40 rounded-xl p-4 text-sm text-red-300">
            Não foi possível carregar o sistema de pagamento. Desative bloqueadores de anúncios ou extensões e recarregue a página.
        </div>
    </template>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Número do cartão</label>
            <input
                type="text"
                x-model="cardNumber"
                @input.debounce.300ms="onCardNumberChange()"
                class="ignis-input"
                placeholder="Número do cartão"
                inputmode="numeric"
                maxlength="19"
                :disabled="scriptBlocked || processing"
            >
            <small class="text-slate-500 text-xs" x-show="brand">Bandeira: <span x-text="brand"></span></small>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Bandeira do cartão</label>
            <select x-model="brand" class="ignis-input" :disabled="scriptBlocked || processing" @change="loadInstallments()">
                <option value="">Selecione</option>
                <option value="visa">Visa</option>
                <option value="mastercard">Mastercard</option>
                <option value="amex">American Express</option>
                <option value="elo">Elo</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Mês de vencimento</label>
            <select x-model="expirationMonth" class="ignis-input" :disabled="scriptBlocked || processing">
                <option value="">Mês</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                    </option>
                @endfor
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Ano de vencimento</label>
            <select x-model="expirationYear" class="ignis-input" :disabled="scriptBlocked || processing">
                <option value="">Ano</option>
                @php $currentYear = date('Y'); @endphp
                @for ($i = 0; $i <= 10; $i++)
                    <option value="{{ $currentYear + $i }}">{{ $currentYear + $i }}</option>
                @endfor
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Código de segurança</label>
            <input
                type="text"
                x-model="securityCode"
                class="ignis-input"
                placeholder="Ex: 123"
                maxlength="4"
                inputmode="numeric"
                :disabled="scriptBlocked || processing"
                @input="securityCode = securityCode.replace(/\D/g, '').slice(0, 4)"
            >
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Nome do titular</label>
            <input
                type="text"
                x-model="holderName"
                class="ignis-input"
                maxlength="100"
                placeholder="Nome do titular"
                :disabled="scriptBlocked || processing"
                @input="holderName = holderName.replace(/[^A-Za-zÀ-ÿ\s]/g, '').toUpperCase().slice(0, 100)"
            >
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">CPF/CNPJ do titular</label>
            <input
                type="text"
                x-model="holderDocument"
                class="ignis-input"
                placeholder="CPF/CNPJ do titular do cartão"
                maxlength="18"
                inputmode="numeric"
                :disabled="scriptBlocked || processing"
                @input="holderDocument = holderDocument.replace(/\D/g, '').slice(0, 14)"
            >
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-300 mb-2">Parcelamento</label>
        <select x-model="installments" class="ignis-input" :disabled="scriptBlocked || processing || installmentsOptions.length === 0">
            <template x-if="installmentsOptions.length === 0">
                <option value="1">Carregando parcelas...</option>
            </template>
            <template x-for="opt in installmentsOptions" :key="opt.installment">
                <option :value="opt.installment" x-text="`${opt.installment}x de R$ ${opt.currency}${opt.has_interest ? ' (com juros)' : ''}`"></option>
            </template>
        </select>
    </div>

    <p x-show="tokenError" class="text-red-400 text-sm" x-text="tokenError"></p>
</div>
