<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    protected $fillable = [
        'cod_inep_aluno',
        'cpf',
        'nome',
        'dt_nascimento',
        'cor',
        'sexo',
        'registro_unico',
        'ano_censo',
        'cod_inep_escola',
        'municipio',
        'uf',
        'escola',
        'modalidade',
        'etapa',
        'cod_turma',
        'nome_turma',
        'estrutura_curricular',
        'tipo_mediacao',
        'tipo_atendimento',
        'localizacao',
        'dependencia',
        'local_funcionamento_turma',
        'dias_semana',
        'horario',
        'forma_organizacao',
        'libras',
        'deficiencia',
        'recursos',
        'created_at',
        'updated_at',
    ];
}
