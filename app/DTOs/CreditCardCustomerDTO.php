<?php

namespace App\DTOs;

readonly class CreditCardCustomerDTO
{
    public function __construct(
        public string $name,
        public string $cpf,
        public string $email,
        public string $phoneNumber,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toEfiArray(): array
    {
        return [
            'name' => $this->name,
            'cpf' => preg_replace('/\D/', '', $this->cpf),
            'email' => $this->email,
            'phone_number' => preg_replace('/\D/', '', $this->phoneNumber),
        ];
    }
}
