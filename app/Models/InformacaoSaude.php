<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InformacaoSaude extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'informacoes_saude';

    protected $fillable = [
        'inscrito_id',
        'restricoes_alimentares',
        'alergias',
        'medicamentos',
        'condicao_saude',
    ];

    public function inscrito(): BelongsTo
    {
        return $this->belongsTo(Inscrito::class, 'inscrito_id');
    }
}
