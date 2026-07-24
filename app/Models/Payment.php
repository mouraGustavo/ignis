<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'evento_inscrito_id',
        'gateway',
        'transaction_id',
        'status',
        'amount',
        'installments',
        'payment_method',
        'authorization_code',
        'card_mask',
        'failure_reason',
        'gateway_response',
        'paid_at',
        'refunded_at',
        'idempotency_key',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'gateway' => PaymentGateway::class,
        'payment_method' => PaymentMethod::class,
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'amount' => 'integer',
        'installments' => 'integer',
    ];

    public function eventoInscrito(): BelongsTo
    {
        return $this->belongsTo(EventoInscrito::class, 'evento_inscrito_id');
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::PAID;
    }

    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }
}
