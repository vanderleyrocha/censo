<?php

declare(strict_types=1);

namespace App\Services;

final class AlunoCensoColumnRegistry
{

    public static function allColumns(): array
    {
        return [
            'cod_inep_escola' => 'Código Inep da escola',
            'nome_escola' => 'Nome da escola',
            'municipio' => 'Município',
            'localizacao_escola' => 'Localização/Zona da escola',
            'dependencia_administrativa' => 'Dependência administrativa',
            'cod_inep_aluno' => 'Identificação única',
            'nome' => 'Nome',
            'data_nascimento' => 'Data de nascimento',
            'cpf' => 'CPF',
            'nacionalidade' => 'Nacionalidade',
            'cor_raca' => 'Cor/Raça',
            'povo_indigena' => 'Povo indígena',
            'sexo' => 'Sexo',
            'tipo_deficiencia' => 'Deficiências / TEA / AH/SD',
            'tipo_transtorno' => 'Transtornos que impactam aprendizagem',
            'recursos_saeb' => 'Recursos (Saeb)',
            'tipo_aee' => 'AEE',
            'escolarizacao_outro_espaco' => 'Escolarização em outro espaço',
            'localizacao_residencia' => 'Localização/Zona de residência',
            'localizacao_diferenciada_residencia' => 'Localização diferenciada',
            'usa_transporte_escolar' => 'Transporte escolar (S/N)',
            'poder_publico_responsavel' => 'Poder público responsável',
            'tipo_veiculo_transporte_escolar' => 'Veículo escolar',
            'etapa_aluno_turma_multi' => 'Etapa (multi)',
            'codigo_matricula' => 'Código matrícula',
            'codigo_turma' => 'Código turma',
            'nome_turma' => 'Nome da turma',
            'tipo_mediacao' => 'Mediação pedagógica',
            'tipo_turma' => 'Tipo da turma',
            'etapa_agregada' => 'Etapa agregada',
            'etapa_ensino' => 'Etapa ensino',
            'formas_organizacao_turma' => 'Formas de organização',
            'local_funcionamento_diferenciado_da_turma' => 'Local diferenciado',
            'dias_semana_horario' => 'Dias e horários',
            'carga_horaria_semanal' => 'Carga horária semanal',
            'classe_especial' => 'Classe especial',
            'classe_bilingue_surdos' => 'Classe bilíngue de surdos',
            'turma_formacao_alternancia' => 'Formação alternância',
            'areas_conhecimento' => 'Áreas do conhecimento',
            'organizacao_curricular_turma' => 'Organização curricular',
            'areas_itinerario_formativo' => 'Áreas itinerário formativo',
            'tipo_curso_ftp' => 'Tipo curso técnico',
            'codido_nome_curso_ftp' => 'Código/nome curso técnico',
            'atividade_complementar' => 'Atividade complementar',
        ];
    }


    public static function presets(): array
    {
        return [
            'geral' => array_keys(self::allColumns()),

            'completo' => [
                
                'municipio',
                'cod_inep_escola',
                'nome_escola',
                'localizacao_escola',
                'dependencia_administrativa',

                'cod_inep_aluno',
                'nome',
                'data_nascimento',
                'cpf',
                'nacionalidade',
                'cor_raca',
                'povo_indigena',
                'sexo',

                'escolarizacao_outro_espaco',
                'localizacao_residencia',
                'localizacao_diferenciada_residencia',

                'usa_transporte_escolar',
                'poder_publico_responsavel',
                'tipo_veiculo_transporte_escolar',

                'etapa_aluno_turma_multi',
                'codigo_matricula',

                'tipo_turma',
                'codigo_turma',
                'nome_turma',
                'tipo_mediacao',
                'formas_organizacao_turma',
                'local_funcionamento_diferenciado_da_turma',
                'etapa_agregada',
                'etapa_ensino',

                'dias_semana_horario',
                'carga_horaria_semanal',

                'tipo_aee',
                'tipo_deficiencia',
                'tipo_transtorno',
                'classe_especial',
                'classe_bilingue_surdos',
                'recursos_saeb',

                'organizacao_curricular_turma',
                'turma_formacao_alternancia',
                'areas_conhecimento',
                'areas_itinerario_formativo',
                'tipo_curso_ftp',
                'codido_nome_curso_ftp',
                'atividade_complementar',
            ],

            'parcial' => [

                'municipio',
                'cod_inep_escola',
                'nome_escola',
                'localizacao_escola',
                'dependencia_administrativa',

                'cod_inep_aluno',
                'nome',
                'data_nascimento',
                'cpf',
                'nacionalidade',
                'cor_raca',
                'povo_indigena',
                'sexo',

                'usa_transporte_escolar',
                'poder_publico_responsavel',
                'tipo_veiculo_transporte_escolar',

                'tipo_turma',
                'codigo_turma',
                'nome_turma',
                'tipo_mediacao',
                'etapa_ensino',

                'dias_semana_horario',
                'carga_horaria_semanal',

                'tipo_curso_ftp',
            ],

            // Exemplo: foco em inclusão
            'inclusao' => [
                'municipio',
                'cod_inep_escola',
                'nome_escola',
                'localizacao_escola',
                'dependencia_administrativa',

                'cod_inep_aluno',
                'nome',
                'data_nascimento',
                'cpf',
                'nacionalidade',
                'cor_raca',
                'povo_indigena',
                'sexo',

                'tipo_turma',
                'codigo_turma',
                'nome_turma',
                'tipo_mediacao',
                'formas_organizacao_turma',
                'local_funcionamento_diferenciado_da_turma',
                'etapa_agregada',
                'etapa_ensino',

                'dias_semana_horario',
                'carga_horaria_semanal',

                'tipo_aee',
                'tipo_deficiencia',
                'tipo_transtorno',
                'classe_especial',
                'classe_bilingue_surdos',
                'recursos_saeb',
            ],

            // Exemplo: foco em transporte escolar
            'transporte' => [
                'municipio',
                'cod_inep_escola',
                'nome_escola',
                'localizacao_escola',
                'dependencia_administrativa',

                'cod_inep_aluno',
                'nome',
                'data_nascimento',
                'cpf',
                'nacionalidade',
                'cor_raca',
                'povo_indigena',
                'sexo',

                'escolarizacao_outro_espaco',
                'localizacao_residencia',
                'localizacao_diferenciada_residencia',

                'usa_transporte_escolar',
                'poder_publico_responsavel',
                'tipo_veiculo_transporte_escolar',

                'tipo_turma',
                'nome_turma',
                'tipo_mediacao',
                'local_funcionamento_diferenciado_da_turma',
                'etapa_agregada',
                'etapa_ensino',

                'dias_semana_horario',
                'carga_horaria_semanal',

                'tipo_aee',
                'tipo_deficiencia',
                'tipo_transtorno',
                'classe_especial',
                'classe_bilingue_surdos',
                'recursos_saeb',

                'organizacao_curricular_turma',
                'turma_formacao_alternancia',
                'atividade_complementar',
            ],
        ];
    }

    public static function resolveColumns(string $preset): array
    {
        $preset = strtolower($preset);

        $all = self::allColumns();
        $presets = self::presets();

        if (!array_key_exists($preset, $presets)) {
            $valid = implode(', ', array_keys($presets));

            throw new \InvalidArgumentException(
                "Preset de colunas inválido: {$preset}. Presets válidos: {$valid}."
            );
        }

        return array_intersect_key($all, array_flip($presets[$preset]));
    }
}
