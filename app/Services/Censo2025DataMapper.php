<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Censo2025DataMapper
{
    private const STUDENT_FIELDS = [
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

    public function extractSchoolData(Worksheet $worksheet): array
    {
        $escolaId = $this->sanitizeCodInep($this->getMergedCellValue($worksheet, 'K14'));
        if (!$this->isValidCodInep($escolaId)) {
            Log::error("Código INEP da escola " . $this->getMergedCellValue($worksheet, 'K15') . " é inválido");
        }
        return [
            'cod_inep_escola' => $escolaId,
            'nome_escola' => $this->getMergedCellValue($worksheet, 'K15'),
            'municipio' => $this->getMergedCellValue($worksheet, 'K17'),
            'localizacao_escola' => $this->getMergedCellValue($worksheet, 'K18'),
            'dependencia_administrativa' => $this->getMergedCellValue($worksheet, 'K19'),
        ];
    }

    public function extractStudentData(Worksheet $worksheet, array $schoolData): array
    {
        $students = [];
        $row = 22; // Início dos dados dos alunos

        // MODIFICAÇÃO: Pré-alocar array com tamanho estimado para melhor performance
        $students = [];

        // MODIFICAÇÃO: Limitar o número máximo de linhas processadas de uma vez
        $maxRows = 5000; // Ajuste conforme necessário

        for ($row = 22; $row <= 22 + $maxRows; $row++) {

            $codInepAluno = $this->getMergedCellValue($worksheet, 'C' . $row);

            // Critério de parada: código INEP do aluno vazio ou inválido
            if (!$this->isValidStudentCodInep($codInepAluno)) {
                break;
            }

            $studentData = ['row' => $row];
            $studentData = array_merge($studentData, $schoolData);

            // MODIFICAÇÃO: Processar apenas colunas necessárias, evitar loop completo
            $keyFields = ['C', 'E', 'H', 'J']; // Campos essenciais primeiro
            foreach ($keyFields as $column) {
                $field = self::STUDENT_FIELDS[$column];
                $value = $this->getMergedCellValue($worksheet, $column . $row);
                $studentData[$field] = $this->formatValue($value, $field);
            }

            // Processar demais campos se necessário
            foreach (self::STUDENT_FIELDS as $column => $field) {
                if (!in_array($column, $keyFields)) {
                    $value = $this->getMergedCellValue($worksheet, $column . $row);
                    $studentData[$field] = $this->formatValue($value, $field);
                }
            }

            $students[] = $studentData;

            // MODIFICAÇÃO: Liberar memória periodicamente
            if ($row % 100 === 0) {
                $this->cleanupMemory();
            }
        }

        return $students;
    }

    private function cleanupMemory(): void
    {
        if (function_exists('gc_mem_caches')) {
            gc_mem_caches();
        }
    }

    private function getMergedCellValue(Worksheet $worksheet, string $cellAddress)
    {
        $cell = $worksheet->getCell($cellAddress);
        $value = $cell->getValue();

        if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
            foreach ($worksheet->getMergeCells() as $mergedRange) {
                if ($cell->isInRange($mergedRange)) {
                    $mergedCellsArray = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::splitRange($mergedRange);
                    $firstCell = $mergedCellsArray[0][0];
                    $value = $worksheet->getCell($firstCell)->getValue();
                    break;
                }
            }
        }

        if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            $value = $value->getPlainText();
        }

        return $value;
    }

    private function isValidStudentCodInep($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $stringValue = trim((string)$value);

        return ctype_digit($stringValue) && strlen($stringValue) >= 8;
    }

    private function isValidCodInep($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $stringValue = trim((string)$value);

        $numericValue = preg_replace('/[^0-9]/', '', $stringValue);

        return strlen($numericValue) === 8 && is_numeric($numericValue);
    }

    private function sanitizeCodInep($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $stringValue = trim((string)$value);

        $numericValue = preg_replace('/[^0-9]/', '', $stringValue);
        if ($numericValue === null || $numericValue === '') {
            return null;
        } else {
            return $numericValue;
        }
    }

    private function formatValue($value, string $field)
    {
        if ($value === null) {
            return null;
        }

        switch ($field) {
            case 'data_nascimento':
                return $this->formatDate($value);
            case 'cpf':
                return $this->formatCpf($value);
            default:
                return is_string($value) ? trim($value) : $value;
        }
    }

    private function formatDate($value): ?string
    {
        if (is_numeric($value)) {
            // Valor numérico do Excel (serial date)
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        if (is_string($value)) {
            try {
                return \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    private function formatCpf($value): ?string
    {
        if (empty($value) || $value === '--') {
            return null;
        }

        $cpf = preg_replace('/[^0-9]/', '', (string)$value);
        return strlen($cpf) === 11 ? $cpf : null;
    }
}
