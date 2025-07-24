<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Escola extends Model
{
    protected $fillable = [
        'nome',
        'dependencia',
        'zona',
        'alunos_simaed',
        'alunos_censo_2024',
        'cidade_id'
    ];

    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Servidor::class, 'responsavel_censo', 'id');
    }
}