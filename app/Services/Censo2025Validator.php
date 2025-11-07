<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Censo2025Validator
{
    private const REQUIRED_HEADERS = [
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

    public function validateStructure(Worksheet $worksheet): array
    {
        $errors = [];

        // Validar células da escola
        $schoolErrors = $this->validateSchoolCells($worksheet);
        $errors = array_merge($errors, $schoolErrors);

        // Validar cabeçalhos
        $headerErrors = $this->validateHeaders($worksheet);
        $errors = array_merge($errors, $headerErrors);

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    // Em Censo2025Validator - método validateSchoolCells
    private function validateSchoolCells(Worksheet $worksheet): array
    {
        $errors = [];

        // K14 - Código INEP (8 dígitos) - MODIFICADO
        $codInep = $this->getMergedCellValue($worksheet, 'K14');
        if (!$this->isValidCodInep($codInep)) {
            $errors[] = 'Célula K14 deve conter um número de 8 dígitos representando o código INEP da escola. Valor encontrado: ' . ($codInep ?? 'vazio') . ' (Tipo: ' . gettype($codInep) . ')';
        }

        // K15 - Nome da escola - MODIFICADO
        $nomeEscola = $this->getMergedCellValue($worksheet, 'K15');
        if (empty(trim($nomeEscola ?? ''))) {
            $errors[] = 'Célula K15 (nome da escola) não pode estar vazia';
        }

        // K17 - Município - MODIFICADO
        $municipio = $this->getMergedCellValue($worksheet, 'K17');
        if (empty(trim($municipio ?? ''))) {
            $errors[] = 'Célula K17 (município) não pode estar vazia';
        }

        // K18 - Localização - MODIFICADO
        $localizacao = $this->getMergedCellValue($worksheet, 'K18');
        if (!in_array($localizacao, ['Urbana', 'Rural'])) {
            $errors[] = 'Célula K18 deve conter "Urbana" ou "Rural". Valor encontrado: ' . ($localizacao ?? 'vazio');
        }

        // K19 - Dependência administrativa - MODIFICADO
        $dependencia = $this->getMergedCellValue($worksheet, 'K19');
        $dependenciasValidas = ['Municipal', 'Estadual', 'Federal', 'Privada'];
        if (!in_array($dependencia, $dependenciasValidas)) {
            $errors[] = 'Célula K19 deve conter "' . implode('", "', $dependenciasValidas) . '". Valor encontrado: ' . ($dependencia ?? 'vazio');
        }

        return $errors;
    }

    private function getMergedCellValue(Worksheet $worksheet, string $cellAddress)
    {
        $cell = $worksheet->getCell($cellAddress);
        $value = $cell->getValue();

        if (empty($value) || (is_string($value) && trim($value) === '')) {
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


    private function validateHeaders(Worksheet $worksheet): array
    {
        $errors = [];

        foreach (self::REQUIRED_HEADERS as $column => $expectedHeader) {
            $actualHeader = $worksheet->getCell($column . '21')->getValue();

            if (trim($actualHeader) !== trim($expectedHeader)) {
                $errors[] = "Cabeçalho da coluna {$column} incorreto. Esperado: '{$expectedHeader}', Encontrado: '{$actualHeader}'";
            }
        }

        return $errors;
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
}
