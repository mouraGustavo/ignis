import Chart from 'chart.js/auto';
import { createEfiPayment } from './efi-payment-token';
import './credit-card-form';

// Exposto globalmente para ser instanciado inline nas views de relatórios
// (resources/views/livewire/admin/relatorios.blade.php), sem precisar de
// mais nenhuma dependência de bundling por página.
window.Chart = Chart;
window.createEfiPayment = createEfiPayment;

// Executa o reCAPTCHA v3 (invisível) para uma action e resolve com o token, ou
// `null` se a chave não estiver configurada ou o script não puder ser carregado
// (bloqueador de anúncios, Google indisponível, etc). O backend decide o que
// fazer com um token nulo (ver App\Services\RecaptchaService).
window.executeRecaptcha = function (siteKey, action) {
    return new Promise((resolve) => {
        if (!siteKey || typeof grecaptcha === 'undefined') {
            resolve(null);
            return;
        }

        grecaptcha.ready(() => {
            grecaptcha.execute(siteKey, { action }).then(resolve).catch(() => resolve(null));
        });
    });
};
