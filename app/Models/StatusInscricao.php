<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusInscricao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'status_inscricao';

    protected $fillable = [
        'nome',
        'descricao',
        'ordem',
    ];

    // Nomes usados como constantes para evitar "magic strings" nos Controllers/Services.
    public const PENDENTE = 'PENDENTE';
    public const AGUARDANDO_PAGAMENTO = 'AGUARDANDO_PAGAMENTO';
    public const CONFIRMADA = 'CONFIRMADA';
    public const CANCELADA = 'CANCELADA';
    public const RECUSADA = 'RECUSADA';
    public const LISTA_ESPERA = 'LISTA_ESPERA';

    public function eventoInscritos(): HasMany
    {
        return $this->hasMany(EventoInscrito::class, 'status_id');
    }
}
