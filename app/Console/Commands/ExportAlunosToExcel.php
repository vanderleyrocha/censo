<?php

namespace App\Console\Commands;

use App\Helpers\Format;
use App\Models\Aluno;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExportAlunosToExcel extends Command
{
    protected $signature = 'export:alunos-excel';
    protected $description = 'Export alunos data to Excel with deduplication';

    public function handle()
    {
        $this->info('Starting export process...');

        $total = Aluno::count();
        $totalWithInep = Aluno::whereNotNull('cod_inep_aluno')->count();
        $this->info("Total: {$total}, Com INEP: {$totalWithInep}");

        ini_set('memory_limit', '4096M');
        set_time_limit(6000); // 1 hora

        // Create spreadsheet instance
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers (without id)
        $headers = [
            'cod_inep_aluno',
            'cpf',
            'nome',
            'dt_nascimento',
            'cor',
            'sexo',
            'cod_inep_escola',
            'escola',
            'modalidade',
            'etapa',
            'cod_turma',
            'nome_turma',
            'tipo_atendimento',
            'tipo_mediacao',
            'municipio',
            'uf'
        ];

        // Apply headers and styling
        $sheet->fromArray($headers, null, 'A1');
        $this->applyHeaderStyles($sheet, count($headers));

        // Use chunking to handle large dataset
        $previousRow = null;
        $rowNumber = 2; // Start from row 2 (after headers)
        $totalProcessed = 0;
        $totalExported = 0;

        $startTime = microtime(true);

        // Get the primary key column name (default is 'id')
        $keyName = (new Aluno())->getKeyName();


        // Include primary key in the select and specify it for chunking
        try {
            Aluno::query()
            ->select(array_merge([$keyName], $headers)) // Include primary key
            ->orderBy('id')
            ->chunkById(5000, function ($alunos) use (&$sheet, &$previousRow, &$rowNumber, &$totalProcessed, &$totalExported, $headers) {
                foreach ($alunos as $aluno) {
                    $totalProcessed++;

                    // Convert to array and keep only the needed columns
                    $currentRow = array_intersect_key($aluno->toArray(), array_flip($headers));

                    // Skip if current row is equal to previous (excluding null values)
                    if ($previousRow && $this->rowsAreEqual($previousRow, $currentRow)) {
                        continue;
                    }

                    // Format specific fields
                    $currentRow['cod_inep_aluno'] = Format::inep($currentRow['cod_inep_aluno'] ?? NULL);
                    $currentRow['cpf'] = Format::cpf($currentRow['cpf'] ?? NULL);
                    $currentRow['dt_nascimento'] = Format::dateBRtoEn($currentRow['dt_nascimento'] ?? NULL);

                    // Add row to spreadsheet
                    try {
                        $sheet->fromArray(array_values($currentRow), null, 'A' . $rowNumber);
                    } catch (\Throwable $e) {
                        $this->error("Error in chunk: " . $e->getMessage());
                    }
                    

                    // Apply text format to INEP column (column A)
                    $sheet->getStyle('A' . $rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

                    $rowNumber++;
                    $totalExported++;

                    $previousRow = $currentRow;
                }

                $this->info("Processed {$totalProcessed} records, exported {$totalExported} unique records...");
            }, $keyName); // Explicitly specify the column to chunk by

           
        } catch (\Throwable $e) {
            $this->error("Export failed: " . $e->getMessage());
        }
            // Auto-size all columns for better visibility
        $this->autoSizeAllColumns($sheet, count($headers));

        // Ensure directory exists
        if (!Storage::exists('exports')) {
            Storage::makeDirectory('exports');
        }

        // Save the file
        $fileName = 'alunos_export_' . now()->format('Ymd_His') . '.xlsx';
        $filePath = Storage::path('exports/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $executionTime = round(microtime(true) - $startTime, 2);

        $this->info("Export completed successfully!");
        $this->info("Total processed: {$totalProcessed} records");
        $this->info("Total exported: {$totalExported} unique records");
        $this->info("Execution time: {$executionTime} seconds");
        $this->info("File saved at: {$filePath}");

        return Command::SUCCESS;
    }

    /**
     * Compare two rows ignoring null values
     */
    protected function rowsAreEqual(array $row1, array $row2): bool
    {
        $equal = true;
        foreach ($row1 as $key => $value) {
            if ($value !== null && isset($row2[$key]) && $row2[$key] !== null) {
                if ($value !== $row2[$key]) {
                    $equal = false;
                    break;
                }
            }
        }
        return $equal;
    }

    /**
     * Apply styling to header row
     */
    protected function applyHeaderStyles($sheet, $columnCount)
    {
        $lastColumnLetter = Coordinate::stringFromColumnIndex($columnCount);
        $headerRange = 'A1:' . $lastColumnLetter . '1';

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Freeze header row
        $sheet->freezePane('A2');
    }

    /**
     * Auto-size all columns
     */
    protected function autoSizeAllColumns($sheet, $columnCount)
    {
        $lastColumnLetter = Coordinate::stringFromColumnIndex($columnCount);

        foreach (range('A', $lastColumnLetter) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
