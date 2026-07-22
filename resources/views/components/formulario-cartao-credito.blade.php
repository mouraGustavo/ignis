<div class="ignis-card space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Número do cartão</label>
            <input
                type="text"
                wire:model.lazy="card_number"
                class="ignis-input"
                placeholder="Número do cartão"
                inputmode="numeric"
                pattern="\d{13,19}"
                maxlength="19"
                minlength="13"
                oninput="
                    let cleaned = this.value.replace(/\D/g, '');
                    if (cleaned.length > 19) cleaned = cleaned.slice(0,19);
                    this.value = cleaned;
                "
            >
            <small class="text-slate-500 text-xs">Apenas números, 13 a 19 dígitos.</small>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Bandeira do cartão</label>
            <select wire:model="card_brand" class="ignis-input">
                <option value="visa">Visa</option>
                <option value="master">Master</option>
                <option value="american_express">American Express</option>
                <option value="elo">Elo</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Mês de vencimento</label>
            <select wire:model="expiration_month" class="ignis-input">
                <option value="">Selecione o mês</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                    </option>
                @endfor
            </select>
        </div>
   
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Ano de vencimento</label>
            <select wire:model="expiration_year" class="ignis-input">
                <option value="">Selecione o ano</option>
                @php
                    $currentYear = date('Y');
                @endphp
                @for ($i = 0; $i <= 10; $i++)
                    <option value="{{ $currentYear + $i }}">{{ $currentYear + $i }}</option>
                @endfor
            </select>
        </div>
   
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Código de segurança</label>
            <input
                type="text"
                wire:model="security_code"
                class="ignis-input"
                placeholder="Ex: 123"
                maxlength="4"
                inputmode="numeric"
                pattern="\d{3,4}"
                oninput="this.value = this.value.replace(/\D/g, '').slice(0, 4)"
            >
        </div>
   
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Nome do titular</label>
            <input type="text" wire:model="card_holder_name" class="ignis-input" placeholder="Nome do titular">
        </div>
        
       <div
    x-data="{
        value: @entangle('card_holder_cpf_cnpj').defer,

        get numbers() {
            return (this.value || '').replace(/\D/g, '').slice(0, 14);
        },

        get type() {
            if (this.numbers.length <= 11) return 'cpf';
            return 'cnpj';
        },

        get isValid() {
            if (this.type === 'cpf' && this.numbers.length === 11) {
                return this.validateCPF(this.numbers);
            }

            if (this.type === 'cnpj' && this.numbers.length === 14) {
                return this.validateCNPJ(this.numbers);
            }

            return null;
        },

        validateCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');

            if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) {
                return false;
            }

            let sum = 0;

            for (let i = 0; i < 9; i++) {
                sum += Number(cpf[i]) * (10 - i);
            }

            let digit = 11 - (sum % 11);
            digit = digit >= 10 ? 0 : digit;

            if (digit !== Number(cpf[9])) {
                return false;
            }

            sum = 0;

            for (let i = 0; i < 10; i++) {
                sum += Number(cpf[i]) * (11 - i);
            }

            digit = 11 - (sum % 11);
            digit = digit >= 10 ? 0 : digit;

            return digit === Number(cpf[10]);
        },

        validateCNPJ(cnpj) {
            cnpj = cnpj.replace(/\D/g, '');

            if (cnpj.length !== 14 || /^(\d)\1+$/.test(cnpj)) {
                return false;
            }

            const calc = (length) => {
                let numbers = cnpj.substring(0, length);
                let sum = 0;
                let pos = length - 7;

                for (let i = length; i >= 1; i--) {
                    sum += Number(numbers[length - i]) * pos--;
                    if (pos < 2) pos = 9;
                }

                return sum % 11 < 2 ? 0 : 11 - (sum % 11);
            };

            return calc(12) === Number(cnpj[12])
                && calc(13) === Number(cnpj[13]);
        }
    }"
>
    <label class="block text-sm font-medium text-slate-300 mb-2">
        CPF/CNPJ do titular
    </label>

    <input
        type="text"
        class="ignis-input"
        placeholder="CPF/CNPJ do titular do cartão"
        x-model="value"
        @input="value = numbers"
        maxlength="14"
        inputmode="numeric"
        pattern="[0-9]*"
    >

    <template x-if="isValid !== null">
        <span
            class="text-xs"
            :class="isValid ? 'text-green-400' : 'text-red-400'"
            x-text="`${type.toUpperCase()} ${isValid ? 'válido' : 'inválido'}`"
        ></span>
    </template>
</div>

    <div>
        <label class="block text-sm font-medium text-slate-300 mb-2">Parcelamento</label>
        <select wire:model="installments" class="ignis-input">
            <option value="1">1x de R$ {{ number_format($value, 2, ',', '.') }}</option>
            <option value="2">2x de R$ {{ number_format($value / 2, 2, ',', '.') }}</option>
        </select>
    </div>
</div>
