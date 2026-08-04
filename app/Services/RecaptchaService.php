<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Valida um token do reCAPTCHA v3 contra a API do Google.
     *
     * Sem `RECAPTCHA_SECRET_KEY` configurada (ex: dev/local), a verificacao e
     * pulada e a requisicao e sempre considerada legitima. Se o Google estiver
     * indisponivel, tambem deixamos passar — o objetivo e barrar bots, nao
     * derrubar o cadastro por uma instabilidade externa.
     */
    public function verify(?string $token, string $action): bool
    {
        $secretKey = config('services.recaptcha.secret_key');

        if (! $secretKey) {
            return true;
        }

        if (! $token) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post(self::VERIFY_URL, [
                'secret' => $secretKey,
                'response' => $token,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao contatar a API do reCAPTCHA, requisicao liberada.', [
                'error' => $e->getMessage(),
            ]);

            return true;
        }

        $result = $response->json() ?? [];

        if (! ($result['success'] ?? false)) {
            return false;
        }

        if (($result['action'] ?? null) !== $action) {
            return false;
        }

        $score = (float) ($result['score'] ?? 0);
        $threshold = (float) config('services.recaptcha.score_threshold', 0.5);

        return $score >= $threshold;
    }
}
