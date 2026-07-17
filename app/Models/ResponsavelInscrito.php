<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResponsavelInscrito extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'responsaveis_inscrito';

    protected $fillable = [
        'inscrito_id',
        'nome',
        'cpf',
        'parentesco',
        'telefone',
        'email',
        'autorizado',
        'documento_url',
    ];

    protected $casts = [
        'autorizado' => 'boolean',
    ];

    public function inscrito(): BelongsTo
    {
        return $this->belongsTo(Inscrito::class, 'inscrito_id');
    }
}
