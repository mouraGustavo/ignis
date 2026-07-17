<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventoInscrito extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evento_inscritos';

    protected $fillable = [
        'evento_id',
        'inscrito_id',
        'status_id',
        'numero_inscricao',
        'pagamento_confirmado',
        'presente',
        'metodo_pagamento',
        'comprovante_pagamento_url',
        'parcelas_pagamento',
        'observacoes',
    ];

    protected $casts = [
        'pagamento_confirmado' => 'boolean',
        'presente' => 'boolean',
        'parcelas_pagamento' => 'integer',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function inscrito(): BelongsTo
    {
        return $this->belongsTo(Inscrito::class, 'inscrito_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusInscricao::class, 'status_id');
    }
}
