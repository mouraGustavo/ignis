<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case AUTHORIZED = 'authorized';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case CHARGEBACK = 'chargeback';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::AUTHORIZED => 'Autorizado',
            self::PAID => 'Pago',
            self::FAILED => 'Recusado',
            self::CANCELLED => 'Cancelado',
            self::REFUNDED => 'Estornado',
            self::CHARGEBACK => 'Chargeback',
            self::EXPIRED => 'Expirado',
        };
    }

    public static function fromEfiStatus(string $efiStatus): self
    {
        return match ($efiStatus) {
            'approved', 'paid' => self::PAID,
            'waiting', 'new' => self::AUTHORIZED,
            'unpaid' => self::FAILED,
            'canceled', 'cancelled' => self::CANCELLED,
            'refunded' => self::REFUNDED,
            'contested' => self::CHARGEBACK,
            'expired' => self::EXPIRED,
            default => self::PENDING,
        };
    }
}
