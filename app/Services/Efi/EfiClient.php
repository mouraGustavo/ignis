<?php

namespace App\Services\Efi;

use App\Exceptions\EfiApiException;
use Efi\EfiPay;
use Efi\Exception\EfiException;
use Illuminate\Support\Facades\Log;

class EfiClient
{
    private ?EfiPay $api = null;

    /**
     * @return array<string, mixed>
     */
    public function options(): array
    {
        $notificationUrl = config('efi.notification_url')
            ?: route('webhooks.efi');

        return [
            'clientId' => config('efi.client_id'),
            'clientSecret' => config('efi.client_secret'),
            'certificate' => config('efi.certificate'),
            'pwdCertificate' => config('efi.pwd_certificate'),
            'sandbox' => (bool) config('efi.sandbox'),
            'debug' => (bool) config('efi.debug'),
            'cache' => (bool) config('efi.cache'),
            'timeout' => (int) config('efi.timeout'),
            'responseHeaders' => false,
        ];
    }

    public function api(): EfiPay
    {
        if ($this->api === null) {
            $this->api = new EfiPay($this->options());
        }

        return $this->api;
    }

    /**
     * @param  callable(EfiPay): mixed  $callback
     */
    public function call(callable $callback): mixed
    {
        try {
            return $callback($this->api());
        } catch (EfiException $e) {
            Log::warning('Efí API error', [
                'code' => $e->code,
                'error' => $e->error,
                'description' => $this->maskSensitive($e->errorDescription),
            ]);

            throw new EfiApiException(
                message: is_string($e->errorDescription) ? $e->errorDescription : ($e->error ?? 'Erro na API Efí'),
                code: (int) $e->code,
                previous: $e,
                error: $e->error,
                errorDescription: $e->errorDescription,
            );
        }
    }

    private function maskSensitive(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return preg_replace('/payment_token["\']?\s*[:=]\s*["\']?[\w]+/i', 'payment_token=***', $value);
    }
}
