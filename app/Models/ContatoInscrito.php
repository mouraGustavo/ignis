<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContatoInscrito extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contatos_inscrito';

    protected $fillable = [
        'inscrito_id',
        'email',
        'telefone',
        'nome_emergencia',
        'telefone_emergencia',
    ];

    public function inscrito(): BelongsTo
    {
        return $this->belongsTo(Inscrito::class, 'inscrito_id');
    }
}
