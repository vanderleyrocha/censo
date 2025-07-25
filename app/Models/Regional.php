<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regional extends Model
{
    protected $table = 'regionais';

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
