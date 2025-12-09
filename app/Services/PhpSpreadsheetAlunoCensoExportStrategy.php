<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\AlunoCensoFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class PhpSpreadsheetAlunoCensoExportStrategy extends AbstractAlunoCensoExportStrategy
{
    public function export(
        Builder $query,
        AlunoCensoFilter $filter,
        ?ExportProgressBar $progressBar = null
    ): string {
        // Colunas definidas via preset no comando
        $columns = $filter->columns;

        // Segurança: se vier vazio, usa preset "geral"
        if (empty($columns)) {
            $columns = AlunoCensoColumnRegistry::resolveColumns('geral');
        }

        $headerKeys = array_keys($columns);
        $headerLabels = array_values($columns);

        $fileName = "exports/alunos_censo_{$filter->ano}_" . now()->format('Ymd_His') . ".xlsx";
        $fullPath = Storage::path($fileName);
        @mkdir(dirname($fullPath), 0775, true);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Quebra automática de linha
        $sheet
            ->getStyle($sheet->calculateWorksheetDimension())
            ->getAlignment()
            ->setWrapText(true);

        $totalColumns = count($headerLabels);
        $lastColumnLetter = Coordinate::stringFromColumnIndex($totalColumns);

        // Controle de largura aproximada
        $maxColumnLengths = [];
        for ($i = 1; $i <= $totalColumns; $i++) {
            $maxColumnLengths[$i] = 0;
        }

        $rowIndex = 1;

        // Linhas decorativas
        $decorativeRows = [
            'Departamento de Dados e Estatísticas Educacionais',
            'Divisão de Censo Escolar',
            "Relação de Alunos do Censo Escolar - {$filter->ano}",
            $this->buildFiltersLabel($filter),
        ];

        foreach ($decorativeRows as $text) {
            $cell = "A{$rowIndex}";
            $sheet->setCellValue($cell, $text);

            // Mesclar da coluna A até a última
            $sheet->mergeCells("A{$rowIndex}:{$lastColumnLetter}{$rowIndex}");

            // Estilo do título
            $sheet->getStyle("A{$rowIndex}")
                ->getFont()
                ->setBold(true)
                ->setSize(14);

            // Atualiza larguras com base na col A
            $len = mb_strlen((string) $text);
            $maxColumnLengths[1] = max($maxColumnLengths[1], $len);

            $rowIndex++;
        }

        $rowIndex++; // linha em branco

        // Cabeçalho das colunas
        $headerRow = $rowIndex;
        $colIndex = 1;

        foreach ($headerLabels as $label) {
            $cell = Coordinate::stringFromColumnIndex($colIndex) . $headerRow;
            $sheet->setCellValue($cell, $label);

            $sheet->getStyle($cell)
                ->getFont()
                ->setBold(true)
                ->setSize(11);

            $len = mb_strlen((string) $label);
            $maxColumnLengths[$colIndex] = max($maxColumnLengths[$colIndex], $len);

            $colIndex++;
        }

        // AutoFilter
        $sheet->setAutoFilter("A{$headerRow}:{$lastColumnLetter}{$headerRow}");

        $rowIndex++;

        // Dados (chunkado)
        $query->chunk(5000, function ($alunos) use ($sheet, &$rowIndex, $headerKeys, $progressBar, &$maxColumnLengths) {
            foreach ($alunos as $aluno) {
                $colIndex = 1;

                foreach ($headerKeys as $col) {
                    $val = $aluno->{$col} ?? null;

                    if ($col === 'cpf' && $val) {
                        $val = $this->formatCpf((string) $val);
                    }

                    if (in_array($col, ['data_nascimento', 'nascimento'], true) && $val) {
                        $val = $this->formatDate((string) $val);
                    }

                    $cell = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                    $sheet->setCellValueExplicit($cell, (string) $val, DataType::TYPE_STRING);

                    $len = mb_strlen((string) $val);
                    $maxColumnLengths[$colIndex] = max($maxColumnLengths[$colIndex], $len);

                    $colIndex++;
                }

                $rowIndex++;
                $progressBar?->advance();
            }
        });

        // Ajuste de largura das colunas
        $maxWidth = 100;

        for ($i = 1; $i <= $totalColumns; $i++) {
            $columnLetter = Coordinate::stringFromColumnIndex($i);
            $length = $maxColumnLengths[$i] ?? 0;

            $width = min($length + 2, $maxWidth);
            $sheet->getColumnDimension($columnLetter)->setWidth($width);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);

        return $fileName;
    }
}
