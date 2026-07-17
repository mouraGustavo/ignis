<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'eventos';

    protected $fillable = [
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'local',
        'valor_inscricao',
        'limite_vagas',
        'idade_minima',
        'ativo',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'valor_inscricao' => 'decimal:2',
        'limite_vagas' => 'integer',
        'idade_minima' => 'integer',
        'ativo' => 'boolean',
    ];

    public function eventoInscritos(): HasMany
    {
        return $this->hasMany(EventoInscrito::class, 'evento_id');
    }

    /**
     * Evento ativo atual. O sistema assume um unico evento ativo por vez
     * (conforme especificacao funcional), entao pegamos o mais recente ativo.
     */
    public static function atual(): ?self
    {
        return static::query()->where('ativo', true)->latest('data_inicio')->first();
    }

    /**
     * Total de inscricoes confirmadas (usado para bloquear novas inscricoes ao
     * atingir o limite_vagas, conforme especificacao funcional).
     */
    public function totalConfirmadas(): int
    {
        return $this->eventoInscritos()
            ->whereHas('status', fn ($q) => $q->where('nome', StatusInscricao::CONFIRMADA))
            ->count();
    }

    public function vagasDisponiveis(): int
    {
        return max(0, $this->limite_vagas - $this->totalConfirmadas());
    }

    public function atingiuLimiteVagas(): bool
    {
        return $this->vagasDisponiveis() <= 0;
    }
}
