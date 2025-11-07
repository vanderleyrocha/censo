<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportCenso2025CorrecoesArray extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-censo2025-correcoes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $colunas = [
            'B' => 'Ordem',
            'C' => 'Identificação única',
            'E' => 'Nome',
            'H' => 'Data de nascimento',
            'J' => 'CPF',
            'L' => 'Nacionalidade',
            'M' => 'Cor/Raça',
            'N' => 'Povo indígena',
            'O' => 'Sexo',
            'P' => 'Tipo(s) de deficiência(s), transtorno(s) do espectro autista e altas habilidades ou superdotação',
            'Q' => 'Tipo(s) de transtorno(s) que impacta(m) o desenvolvimento da aprendizagem',
            'R' => 'Recursos para o uso do(a) aluno(a) em sala de aula para a participação em avaliações do Inep (Saeb)',
            'S' => 'Tipo de atendimento educacional especializado',
            'T' => 'Recebe escolarização em outro espaço (diferente da escola)',
            'U' => 'Localização/Zona de residência',
            'V' => 'Localização diferenciada de residência',
            'W' => 'Transporte escolar (Sim/Não)',
            'X' => 'Poder Público responsável',
            'Y' => 'Tipo de veículo utilizado no transporte escolar',
            'Z' => 'Etapa de vínculo do(a) aluno(a) (Para turmas do tipo multi)',
            'AA' => 'Código da Matrícula',
            'AB' => 'Código da turma',
            'AC' => 'Nome da turma',
            'AD' => 'Tipo de mediação didático-pedagógica',
            'AE' => 'Tipo de turma',
            'AF' => 'Etapa Agregada',
            'AG' => 'Etapa de ensino',
            'AH' => 'Formas de organização da turma',
            'AI' => 'Local de funcionamento diferenciado da turma',
            'AJ' => 'Dias da semana e horário de funcionamento',
            'AK' => 'Carga horária semanal (hh:mm)',
            'AL' => 'Turma de educação especial (classe especial)',
            'AM' => 'Turma de Educação Bilingue de Surdos (classe bilingue de surdos)',
            'AN' => 'Turma de Formação por Alternância (proposta pedagógica de formação por alternância: tempo - escola e tempo - comunidade)',
            'AO' => 'Áreas do conhecimento/componentes curriculares',
            'AP' => 'Organização curricular da turma',
            'AQ' => 'Área(s) do itinerário formativo',
            'AR' => 'Tipo do curso do itinerário de formação técnica e profissional',
            'AS' => 'Código e nome do curso técnico',
            'AT' => 'Atividade(s) complementar(es)'
        ];
        $fields = [
            'B' => 'ordem',
            'C' => 'cod_inep_aluno',
            'E' => 'nome',
            'H' => 'data_nascimento',
            'J' => 'cpf',
            'L' => 'nacionalidade',
            'M' => 'cor_raca',
            'N' => 'povo_indigena',
            'O' => 'sexo',
            'P' => 'tipo_deficiencia',
            'Q' => 'tipo_transtorno',
            'R' => 'recursos_saeb',
            'S' => 'tipo_aee',
            'T' => 'escolarizacao_outro_espaco',
            'U' => 'localizacao_residencia',
            'V' => 'localizacao_diferenciada_residencia',
            'W' => 'usa_transporte_escolar',
            'X' => 'poder_publico_responsavel',
            'Y' => 'tipo_veiculo_transporte_escolar',
            'Z' => 'etapa_aluno_turma_multi',
            'AA' => 'codigo_matricula',
            'AB' => 'codigo_turma',
            'AC' => 'nome_turma',
            'AD' => 'tipo_mediacao',
            'AE' => 'tipo_turma',
            'AF' => 'etapa_agregada',
            'AG' => 'etapa_ensino',
            'AH' => 'formas_organizacao_turma',
            'AI' => 'local_funcionamento_diferenciado_da_turma',
            'AJ' => 'dias_semana_horario',
            'AK' => 'carga_horaria_semanal',
            'AL' => 'classe_especial',
            'AM' => 'classe_bilingue_surdos',
            'AN' => 'turma_formacao_alternancia',
            'AO' => 'areas_conhecimento',
            'AP' => 'organizacao_curricular_turma',
            'AQ' => 'areas_itinerario_formativo',
            'AR' => 'tipo_curso_ftp',
            'AS' => 'codido_nome_curso_ftp',
            'AT' => 'atividade_complementar'
        ];
    }
}
