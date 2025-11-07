<?php

namespace App\Services;

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
        return [
            'cod_inep_escola' => $this->getMergedCellValue($worksheet, 'K14'),
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

        while (true) {
            $codInepAluno = $this->getMergedCellValue($worksheet, 'C' . $row);

            // Critério de parada: código INEP do aluno com menos de 7 dígitos
            if (!$this->isValidStudentCodInep($codInepAluno)) {
                break;
            }

            $studentData = ['row' => $row];

            // Dados da escola
            $studentData = array_merge($studentData, $schoolData);

            // Dados do aluno - usando getMergedCellValue para todas as células
            foreach (self::STUDENT_FIELDS as $column => $field) {
                $value = $this->getMergedCellValue($worksheet, $column . $row);
                $studentData[$field] = $this->formatValue($value, $field);
            }

            $students[] = $studentData;
            $row++;

            // Limitar memória processando em lotes muito grandes
            if ($row > 10000) {
                break;
            }
        }

        return $students;
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

        $numericValue = preg_replace('/[^0-9]/', '', $stringValue);

        return strlen($numericValue) >= 8 && is_numeric($numericValue);
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
