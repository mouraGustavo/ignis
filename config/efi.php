<?php

return [
    'sandbox' => env('EFI_SANDBOX', true),

    'client_id' => env('EFI_CLIENT_ID', ''),

    'client_secret' => env('EFI_CLIENT_SECRET', ''),

    'certificate' => env('EFI_CERTIFICATE_PATH')
        ? base_path(env('EFI_CERTIFICATE_PATH'))
        : storage_path('app/private/efi/certificate.p12'),

    'pwd_certificate' => env('EFI_CERTIFICATE_PASSWORD', ''),

    'payee_code' => env('EFI_PAYEE_CODE', ''),

    'notification_url' => env('EFI_NOTIFICATION_URL'),

    'timeout' => (int) env('EFI_TIMEOUT', 30),

    'cache' => env('EFI_CACHE', true),

    'debug' => env('EFI_DEBUG', false),
];
