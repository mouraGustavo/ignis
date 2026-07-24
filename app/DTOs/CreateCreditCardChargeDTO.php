<?php

namespace App\DTOs;

readonly class CreateCreditCardChargeDTO
{
    public function __construct(
        public int $eventoInscritoId,
        public string $paymentToken,
        public string $cardMask,
        public int $installments,
        public int $amountCents,
        public CreditCardCustomerDTO $customer,
        public string $itemName,
        public string $customId,
    ) {
    }
}
