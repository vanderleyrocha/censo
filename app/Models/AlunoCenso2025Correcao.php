<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlunoCenso2025Correcao extends Model
{
    protected $table = 'aluno_censo_2025_correcao';
    protected $fillable = [
        'cod_inep_escola',
        'nome_escola',
        'municipio',
        'localizacao_escola',
        'dependencia_administrativa',
        'ordem',
        'cod_inep_aluno',
        'nome',
        'data_nascimento',
        'cpf',
        'nacionalidade',
        'cor_raca',
        'povo_indigena',
        'sexo',
        'tipo_deficiencia',
        'tipo_transtorno',
        'recursos_saeb',
        'tipo_aee',
        'escolarizacao_outro_espaco',
        'localizacao_residencia',
        'localizacao_diferenciada_residencia',
        'usa_transporte_escolar',
        'poder_publico_responsavel',
        'tipo_veiculo_transporte_escolar',
        'etapa_aluno_turma_multi',
        'codigo_matricula',
        'codigo_turma',
        'nome_turma',
        'tipo_mediacao',
        'tipo_turma',
        'etapa_agregada',
        'etapa_ensino',
        'formas_organizacao_turma',
        'local_funcionamento_diferenciado_da_turma',
        'dias_semana_horario',
        'carga_horaria_semanal',
        'classe_especial',
        'classe_bilingue_surdos',
        'turma_formacao_alternancia',
        'areas_conhecimento',
        'organizacao_curricular_turma',
        'areas_itinerario_formativo',
        'tipo_curso_ftp',
        'codido_nome_curso_ftp',
        'atividade_complementar'
    ];

    public $timestamps = true;
}
