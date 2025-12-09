<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluno2023 extends Model
{
    use HasFactory;

    protected $table = 'alunos_censo_2023';

    protected $fillable = [
        'ano_censo',
        'municipio',
        'rede',
        'localizacao',
        'cod_inep',
        'escola',
        'etapa',
        'turma',
        'nome',
        'cpf',
        'sexo',
        'dia',
        'mes',
        'ano',
        'nascimento',
        'mae',
    ];

    protected $casts = [
        'id'         => 'integer',
        'ano_censo'  => 'integer',
        'cod_inep'   => 'integer',
        'dia'        => 'integer',
        'mes'        => 'integer',
        'ano'        => 'integer',

        // strings
        'municipio'  => 'string',
        'rede'       => 'string',
        'localizacao' => 'string',
        'escola'     => 'string',
        'etapa'      => 'string',
        'turma'      => 'string',
        'nome'       => 'string',
        'cpf'        => 'string',
        'sexo'       => 'string',
        'nascimento' => 'string',
        'mae'        => 'string',
    ];
}
