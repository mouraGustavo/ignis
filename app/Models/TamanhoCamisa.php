<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TamanhoCamisa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tamanhos_camisa';

    protected $fillable = [
        'tamanho',
    ];

    public function inscritos(): HasMany
    {
        return $this->hasMany(Inscrito::class, 'tamanho_camisa_id');
    }
}
