<?php

namespace App\Services\Efi;

/**
 * O SDK Efí gerencia OAuth e cache de token internamente (FileCacheRetriever).
 * Esta classe expõe o ponto de extensão para testes e manutenção.
 */
class OAuthService
{
    public function __construct(
        private readonly EfiClient $efiClient,
    ) {
    }

    public function client(): EfiClient
    {
        return $this->efiClient;
    }

    /**
     * Força nova instância do client (útil em testes após alterar credenciais).
     */
    public function reset(): void
    {
        // EfiClient é singleton por request; reset via rebind no container em testes.
    }
}
