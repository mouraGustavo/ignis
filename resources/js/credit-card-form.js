export function creditCardForm(config) {
    return {
        valueCents: config.valueCents,
        payeeCode: config.payeeCode,
        environment: config.environment,
        maxInstallments: config.maxInstallments,
        efi: null,
        scriptBlocked: false,
        processing: false,
        cardNumber: '',
        brand: '',
        expirationMonth: '',
        expirationYear: '',
        securityCode: '',
        holderName: '',
        holderDocument: '',
        installments: 1,
        installmentsOptions: [],
        tokenError: null,

        async init() {
            if (!this.payeeCode) {
                this.tokenError = 'Pagamento por cartão temporariamente indisponível.';
                return;
            }

            if (typeof window.createEfiPayment !== 'function') {
                this.tokenError = 'Sistema de pagamento não carregado. Recarregue a página.';
                return;
            }

            this.efi = window.createEfiPayment({
                payeeCode: this.payeeCode,
                environment: this.environment,
            });

            try {
                this.scriptBlocked = await this.efi.checkScriptBlocked();
            } catch (e) {
                this.tokenError = 'Erro ao inicializar pagamento.';
            }
        },

        async onCardNumberChange() {
            this.cardNumber = this.cardNumber.replace(/\D/g, '').slice(0, 19);
            if (this.cardNumber.length < 6) return;

            try {
                const detected = await this.efi.identifyBrand(this.cardNumber);
                if (detected && detected !== 'undefined' && detected !== 'unsupported') {
                    this.brand = detected;
                    await this.loadInstallments();
                }
            } catch (e) {
                // bandeira não identificada
            }
        },

        async loadInstallments() {
            if (!this.brand || !this.efi) return;

            try {
                const options = await this.efi.getInstallments(this.brand, this.valueCents);
                this.installmentsOptions = options.filter(o => o.installment <= this.maxInstallments);
                if (this.installmentsOptions.length > 0) {
                    this.installments = this.installmentsOptions[0].installment;
                }
            } catch (e) {
                this.installmentsOptions = [{
                    installment: 1,
                    has_interest: false,
                    currency: (this.valueCents / 100).toFixed(2).replace('.', ','),
                }];
            }
        },

        async tokenize() {
            this.tokenError = null;

            if (!this.brand) {
                this.tokenError = 'Selecione a bandeira do cartão.';
                return null;
            }

            if (!this.cardNumber || !this.expirationMonth || !this.expirationYear || !this.securityCode) {
                this.tokenError = 'Preencha todos os dados do cartão.';
                return null;
            }

            if (!this.holderName.trim() || !this.holderDocument) {
                this.tokenError = 'Informe o titular e CPF/CNPJ do cartão.';
                return null;
            }

            this.processing = true;

            try {
                return await this.efi.generatePaymentToken({
                    brand: this.brand,
                    number: this.cardNumber,
                    cvv: this.securityCode,
                    expirationMonth: this.expirationMonth,
                    expirationYear: String(this.expirationYear),
                    holderName: this.holderName.trim(),
                    holderDocument: this.holderDocument,
                });
            } catch (e) {
                this.tokenError = e?.error_description || e?.message || 'Não foi possível validar o cartão.';
                return null;
            } finally {
                this.processing = false;
            }
        },
    };
}

document.addEventListener('alpine:init', () => {
    Alpine.data('creditCardForm', creditCardForm);
});
