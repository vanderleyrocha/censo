<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servidor extends Model
{
    protected $table = 'servidores';

    use HasFactory;

    protected $fillable = [
        'matricula',
        'contrato1',
        'contrato2',
        'nome',
        'cargo',
        'funcao',
        'lotacao',
        'usuario',
        'email',
    ];

    // Relação com o usuário (se aplicável)
    public function user()
    {
        return $this->hasOne(User::class, 'servidor_id');
    }

    public function regionais()
    {
        return $this->hasMany(Regional::class, 'servidor_id');
    }

    public function regioes()
    {
        return $this->hasMany(Regiao::class, 'servidor_id');
    }
}
