<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluno2025 extends Model
{
    use HasFactory;

    protected $table = 'alunos_censo_2025';
    
    protected $fillable = [
        'cod_inep_escola',
        'nome_escola',
        'municipio',
        'localizacao_escola',
        'dependencia_administrativa',
        'cod_inep_aluno',
        'nome',
        'nascimento',
        'cpf',
        'nacionalidade',
        'raca',
        'povo_indigena',
        'sexo',
        'tipo_deficiencia',
        'transtorno_aprendizagem',
        'recursos_saeb',
        'tipo_atendimento_especializado',
        'espaco_diferente',
        'localizacao_residencia',
        'localizacao_diferente',
        'transporte_escolar',
        'transporte_publico',
        'tipo_veiculo',
        'etapa_vinculo',
        'codigo_matricula',
        'codigo_turma',
        'nome_turma',
        'tipo_mediacao',
        'etapa_agregada',
        'etapa_ensino',
        'forma_organizacao',
        'local_diferenciado',
        'horario_funcionamento',
        'carga_horaria_semanal',
        'turma_aee',
        'turma_bilingue',
        'turma_alternancia',
        'area_conhecimento',
        'organizacao_curricular',
        'itinerario_formativo',
        'tipo_ftp',
        'codigo_nome_ftp',
        'atividade_complementar',
    ];
}
