/**
 * Wrapper para tokenização de cartão via biblioteca Efí.
 * Carrega via CDN (window.EfiPay) em pagamento.blade.php.
 */

function getEfiPay() {
    if (typeof window.EfiPay === 'undefined') {
        throw new Error('Biblioteca de pagamento Efí não carregada.');
    }

    return window.EfiPay;
}

export function createEfiPayment(config) {
    const { payeeCode, environment } = config;

    return {
        async checkScriptBlocked() {
            const EfiPay = getEfiPay();

            return EfiPay.CreditCard.isScriptBlocked();
        },

        async identifyBrand(cardNumber) {
            const EfiPay = getEfiPay();

            return EfiPay.CreditCard
                .setCardNumber(cardNumber.replace(/\D/g, ''))
                .verifyCardBrand();
        },

        async getInstallments(brand, totalCents) {
            const EfiPay = getEfiPay();

            const result = await EfiPay.CreditCard
                .setAccount(payeeCode)
                .setEnvironment(environment)
                .setBrand(brand)
                .setTotal(totalCents)
                .getInstallments();

            return result?.installments ?? [];
        },

        async generatePaymentToken(cardData) {
            const EfiPay = getEfiPay();

            const result = await EfiPay.CreditCard
                .setAccount(payeeCode)
                .setEnvironment(environment)
                .setCreditCardData({
                    brand: cardData.brand,
                    number: cardData.number.replace(/\D/g, ''),
                    cvv: cardData.cvv,
                    expirationMonth: cardData.expirationMonth,
                    expirationYear: cardData.expirationYear,
                    holderName: cardData.holderName,
                    holderDocument: cardData.holderDocument.replace(/\D/g, ''),
                    reuse: false,
                })
                .getPaymentToken();

            return {
                paymentToken: result.payment_token,
                cardMask: result.card_mask,
            };
        },
    };
}
