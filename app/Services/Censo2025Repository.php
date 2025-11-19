<?php

namespace App\Services;

use App\Models\Cidade;
use App\Models\Escola;
use App\Models\AlunoCenso2025Correcao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function Illuminate\Log\log;

class Censo2025Repository
{

    public function findOrCreateCity(string $municipio): ?Cidade
    {
        // Buscar cidade ignorando acentos e case
        $city = Cidade::where('estado_id', 1)
            ->where(
                DB::raw('LOWER(REPLACE(REPLACE(REPLACE(nome, "á", "a"), "é", "e"), "í", "i"))'),
                Str::lower($this->removeAccents($municipio))
            )
            ->first();

        if (!$city) {
            // Log da cidade não encontrada
            \Log::warning("Cidade não encontrada: {$municipio}");
        }

        return $city;
    }

    public function findOrCreateSchool(array $schoolData, int $cityId): Escola
    {
        // Verificar se a escola já existe
        $existingSchool = Escola::find($schoolData['cod_inep_escola']);

        if ($existingSchool) {
            // Escola encontrada - atualizar campos
            $existingSchool->update([
                // 'id' => $schoolData['cod_inep_escola'],
                // 'nome' => $schoolData['nome_escola'],
                // 'zona' => $schoolData['localizacao_escola'],
                // 'dependencia' => $schoolData['dependencia_administrativa'],
                // 'cidade_id' => $cityId,
                'situacao' => 'Ativa',
                'encontrada' => true, // Marcar como encontrada
                'updated_at' => now()
            ]);
            return $existingSchool;
        } else {
            // Criar nova escola
            Log::info("Escola (" . $schoolData['cod_inep_escola'] . ") não foi encontrada");
            return Escola::create([
                'id' => $schoolData['cod_inep_escola'],
                'nome' => $schoolData['nome_escola'],
                'zona' => $schoolData['localizacao_escola'],
                'dependencia' => $schoolData['dependencia_administrativa'],
                'cidade_id' => $cityId,
                'situacao' => 'Ativa',
                'nova' => true, // Marcar como nova
                'encontrada' => false,
                'total_registros_importados_2025' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function updateSchoolImportStats(int $schoolId, int $recordsImported): void
    {
        Escola::where('id', $schoolId)->update([
            'total_registros_importados_2025' => DB::raw("total_registros_importados_2025 + {$recordsImported}"),
            'updated_at' => now()
        ]);
    }

    public function bulkInsertStudents(array $students, int $chunkSize = 100): int
    {
        if (empty($students)) {
            \Log::warning("Tentativa de inserir array vazio de alunos");
            return 0;
        }

        $totalStudents = count($students);
        // \Log::info("Preparando para inserir {$totalStudents} registros com chunk size: {$chunkSize}");

        // Usar o chunk size passado como parâmetro
        $chunks = array_chunk($students, $chunkSize);
        $totalInserted = 0;

        foreach ($chunks as $index => $chunk) {
            $chunkSize = count($chunk);
            // \Log::info("Processando chunk {$index} com {$chunkSize} registros");

            $preparedData = array_map(function ($student) {
                return $this->prepareStudentData($student);
            }, $chunk);

            try {
                $result = AlunoCenso2025Correcao::insert($preparedData);
                $totalInserted += count($preparedData);
                // \Log::info("Chunk {$index} inserido com sucesso: {$chunkSize} registros (Total acumulado: {$totalInserted}/{$totalStudents})");
            } catch (\Exception $e) {
                \Log::error("Erro ao inserir chunk {$index}: " . $e->getMessage());
                \Log::error("Primeiro registro do chunk: " . json_encode($preparedData[0] ?? []));
                throw $e;
            }

            // Limpar memória
            unset($preparedData);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }

        // \Log::info("Processamento concluído: {$totalInserted} de {$totalStudents} registros inseridos com sucesso");

        return $totalInserted;
    }

    private function prepareStudentData(array $student): array
    {
        $data = [];
        $mapping = [
            'cod_inep_escola' => 'cod_inep_escola',
            'nome_escola' => 'nome_escola',
            'municipio' => 'municipio',
            'localizacao_escola' => 'localizacao_escola',
            'dependencia_administrativa' => 'dependencia_administrativa',
            'ordem' => 'ordem',
            'cod_inep_aluno' => 'cod_inep_aluno',
            'nome' => 'nome',
            'data_nascimento' => 'data_nascimento',
            'cpf' => 'cpf',
            'nacionalidade' => 'nacionalidade',
            'cor_raca' => 'cor_raca',
            'povo_indigena' => 'povo_indigena',
            'sexo' => 'sexo',
            'tipo_deficiencia' => 'tipo_deficiencia',
            'tipo_transtorno' => 'tipo_transtorno',
            'recursos_saeb' => 'recursos_saeb',
            'tipo_aee' => 'tipo_aee',
            'escolarizacao_outro_espaco' => 'escolarizacao_outro_espaco',
            'localizacao_residencia' => 'localizacao_residencia',
            'localizacao_diferenciada_residencia' => 'localizacao_diferenciada_residencia',
            'usa_transporte_escolar' => 'usa_transporte_escolar',
            'poder_publico_responsavel' => 'poder_publico_responsavel',
            'tipo_veiculo_transporte_escolar' => 'tipo_veiculo_transporte_escolar',
            'etapa_aluno_turma_multi' => 'etapa_aluno_turma_multi',
            'codigo_matricula' => 'codigo_matricula',
            'codigo_turma' => 'codigo_turma',
            'nome_turma' => 'nome_turma',
            'tipo_mediacao' => 'tipo_mediacao',
            'tipo_turma' => 'tipo_turma',
            'etapa_agregada' => 'etapa_agregada',
            'etapa_ensino' => 'etapa_ensino',
            'formas_organizacao_turma' => 'formas_organizacao_turma',
            'local_funcionamento_diferenciado_da_turma' => 'local_funcionamento_diferenciado_da_turma',
            'dias_semana_horario' => 'dias_semana_horario',
            'carga_horaria_semanal' => 'carga_horaria_semanal',
            'classe_especial' => 'classe_especial',
            'classe_bilingue_surdos' => 'classe_bilingue_surdos',
            'turma_formacao_alternancia' => 'turma_formacao_alternancia',
            'areas_conhecimento' => 'areas_conhecimento',
            'organizacao_curricular_turma' => 'organizacao_curricular_turma',
            'areas_itinerario_formativo' => 'areas_itinerario_formativo',
            'tipo_curso_ftp' => 'tipo_curso_ftp',
            'codido_nome_curso_ftp' => 'codido_nome_curso_ftp',
            'atividade_complementar' => 'atividade_complementar'
        ];

        foreach ($mapping as $source => $target) {
            $data[$target] = $student[$source] ?? null;
        }

        $data['created_at'] = now();
        $data['updated_at'] = now();

        return $data;
    }

    private function removeAccents(string $string): string
    {
        return strtr(
            utf8_decode($string),
            utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),
            'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'
        );
    }
}
