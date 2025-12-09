<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluno2024 extends Model
{
    use HasFactory;

    protected $table = 'alunos_censo_2024';

    protected $fillable = [
        'ra_simaed',
        'registro_unico',
        'ano_censo',
        'cod_inep_aluno',
        'cpf',
        'cpf_valido',
        'nome',
        'nascimento',
        'cor',
        'deficiencia',
        'sexo',
        'cod_inep_escola',
        'escola',
        'modalidade',
        'etapa',
        'tipo_atendimento',
        'tipo_mediacao',
        'estrutura_curricular',
        'forma_organizacao',
        'cod_turma',
        'nome_turma',
        'local_funcionamento_turma',
        'dias_semana',
        'horario',
        'uf',
        'municipio',
        'localizacao',
        'dependencia',
        'libras',
        'recursos',
    ];

    protected $casts = [
        'id' => 'integer',
        'ra_simaed' => 'integer',
        'registro_unico' => 'boolean',
        'ano_censo' => 'integer',
        'cod_inep_aluno' => 'integer',
        'cpf_valido' => 'boolean',
        'nascimento' => 'date',
        'cod_inep_escola' => 'integer',
        'cod_turma' => 'integer',
        'libras' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
