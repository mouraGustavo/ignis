<?php

namespace App\Services\Efi;

class NotificationService
{
    public function __construct(
        private readonly EfiClient $efiClient,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function resolveNotification(string $token): array
    {
        $response = $this->efiClient->call(
            fn ($api) => $api->getNotification(['token' => $token])
        );

        if (is_object($response)) {
            $response = json_decode(json_encode($response), true);
        }

        $data = $response['data'] ?? $response;

        return is_array($data) ? $data : [];
    }
}
