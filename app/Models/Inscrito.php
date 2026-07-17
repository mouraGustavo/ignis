<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Inscrito extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inscritos';

    protected $fillable = [
        'nome',
        'data_nascimento',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'tamanho_camisa_id',
        'termos_aceitos',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'termos_aceitos' => 'array', // jsonb: { term_truth, term_participation, term_emergency, term_image, term_rules }
    ];

    // --- Relacionamentos ---

    public function tamanhoCamisa(): BelongsTo
    {
        return $this->belongsTo(TamanhoCamisa::class, 'tamanho_camisa_id');
    }

    public function contato(): HasOne
    {
        return $this->hasOne(ContatoInscrito::class, 'inscrito_id');
    }

    public function informacaoSaude(): HasOne
    {
        return $this->hasOne(InformacaoSaude::class, 'inscrito_id');
    }

    public function responsavel(): HasOne
    {
        return $this->hasOne(ResponsavelInscrito::class, 'inscrito_id');
    }

    public function eventoInscritos(): HasMany
    {
        return $this->hasMany(EventoInscrito::class, 'inscrito_id');
    }

    public function eventos(): BelongsToMany
    {
        return $this->belongsToMany(Evento::class, 'evento_inscritos', 'inscrito_id', 'evento_id')
            ->withPivot(['status_id', 'numero_inscricao', 'pagamento_confirmado', 'presente'])
            ->withTimestamps();
    }

    // --- Regras de negocio ---

    /**
     * Idade calculada dinamicamente a partir de data_nascimento (nunca armazenada),
     * conforme definido no guia de migracao.
     */
    protected function idade(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->data_nascimento
                ? Carbon::parse($this->data_nascimento)->age
                : null,
        );
    }

    public function isMenorDeIdade(): bool
    {
        return $this->idade < 18;
    }
}
