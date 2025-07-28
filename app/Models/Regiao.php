<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regiao extends Model
{
    protected $table = 'regioes';

    protected $fillable = [
        'nome',
        'sigla',
        'servidor_id'
    ];

    public function servidor()
    {
        return $this->belongsTo(Servidor::class, 'servidor_id');
    }
}
