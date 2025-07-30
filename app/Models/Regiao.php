<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Regiao extends Model
{
    use HasFactory;

    protected $table = 'regioes';

    protected $fillable = [
        'nome',
        'sigla',
        'servidor_id'
    ];

    public function servidor(): BelongsTo
    {
        return $this->belongsTo(Servidor::class);
    }
}