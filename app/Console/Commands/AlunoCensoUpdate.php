<?php

namespace App\Console\Commands;

use App\Models\Aluno;
use App\Utils\Validate;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AlunoCensoUpdate extends Command
{
    protected $signature = 'aluno-censo:update
        {--registro-unico : Atualiza o campo registro_unico}
        {--valida-cpf : Verifica se o CPF é válido}
    ';
    protected $description = 'Atualiza o campo registro_unico na tabela alunos_censo_2024';

    public function handle()
    {
        $this->info('Iniciando processamento com base nos parâmetros passados');
        $this->newLine();

        $registro_unico = (bool)$this->option('registro-unico');
        $valida_cpf = (bool)$this->option('valida-cpf');

        if ($registro_unico) {
            $this->info('Iniciando processo de atualização de registros únicos...');
            Aluno::query()->update(['registro_unico' => 0]); // Reset all registro_unico to 0
            // Step 1: Update records with unique cod_inep_aluno
            $this->updateUniqueRecords();

            // Step 2-4: Process duplicates
            $this->processDuplicates();
        }

        if ($valida_cpf) {
            $this->verificaCpfValido();
        }

        $this->info('Processo concluído com sucesso!');
    }

    protected function updateUniqueRecords()
    {
        $this->info('Atualizando registros com cod_inep_aluno único...');

        // Get all cod_inep_aluno that appear only once
        $uniqueCodes = Aluno::query()
            ->select('cod_inep_aluno')
            ->groupBy('cod_inep_aluno')
            ->havingRaw('COUNT(*) = 1')
            ->pluck('cod_inep_aluno');

        $totalUnique = $uniqueCodes->count();
        $this->line("Encontrados {$totalUnique} registros com cod_inep_aluno único.");

        $bar = $this->output->createProgressBar($totalUnique);
        $bar->start();

        // Update in chunks for better performance
        $chunkSize = 1000;
        $uniqueCodes->chunk($chunkSize)->each(function ($chunk) use ($bar) {
            Aluno::whereIn('cod_inep_aluno', $chunk)
                ->update(['registro_unico' => 1]);

            $bar->advance($chunk->count());
        });

        $bar->finish();
        $this->newLine();
        $this->info("Atualizados {$totalUnique} registros com cod_inep_aluno único.");
    }

    protected function processDuplicates()
    {
        $this->info('Processando registros duplicados...');
        $startTime = microtime(true);

        // 1. Identificar todos os códigos INEP duplicados
        $duplicateCodes = Aluno::query()
            ->select('cod_inep_aluno')
            ->groupBy('cod_inep_aluno')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('cod_inep_aluno');

        $totalDuplicates = $duplicateCodes->count();
        $this->line("Encontrados {$totalDuplicates} códigos INEP com registros duplicados.");

        // 2. Processar em lotes para melhor performance
        $batchSize = 500;
        $processed = 0;
        $updatedCount = 0;
        $totalRecords = 0;

        $bar = $this->output->createProgressBar($totalDuplicates);
        $bar->setFormat("%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%\nProcessando: %message%");
        $bar->setMessage('Iniciando...');
        $bar->start();

        $duplicateCodes->chunk($batchSize)->each(function ($batch) use ($bar, &$processed, &$updatedCount, &$totalRecords, &$totalDuplicates) {
            // 3. Obter todos os IDs dos registros que devem ser marcados como únicos
            $recordsToUpdate = Aluno::query()
                ->whereIn('cod_inep_aluno', $batch)
                ->selectRaw('id')
                ->whereRaw('id = (SELECT id FROM alunos WHERE cod_inep_aluno = alunos.cod_inep_aluno ORDER BY prioridade DESC, id DESC LIMIT 1)')
                ->pluck('id')
                ->toArray();

            // 4. Atualização em massa
            if (!empty($recordsToUpdate)) {
                $count = Aluno::whereIn('id', $recordsToUpdate)
                    ->update(['registro_unico' => 1]);

                $updatedCount += $count;
                $totalRecords += $batch->count();
            }

            $processed += $batch->count();
            $bar->setMessage("Processados {$processed}/{$totalDuplicates} códigos | Atualizados {$updatedCount} registros");
            $bar->advance($batch->count());
        });

        $bar->finish();
        $this->newLine();

        $executionTime = round(microtime(true) - $startTime, 2);
        $this->info("Processados {$totalDuplicates} códigos INEP com duplicatas em {$executionTime} segundos.");
        $this->info("Atualizados {$updatedCount} registros (primeiro de cada duplicata).");

        // Gerar relatório
        $this->generateReport($totalDuplicates, $updatedCount);
    }
    protected function verificaCpfValido()
    {
        $ids_cpf_valido = [];
        $ids_cpf_invalido = [];

        $alunos = Aluno::select(["id", "cpf"])->get();

        $total = $alunos->count();
        $this->line("Encontrados {$total} registros.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // Update in chunks for better performance
        $chunkSize = 1000;

        foreach ($alunos as $aluno) {
            if (Validate::cpf($aluno->cpf)) {
                $ids_cpf_valido[] = $aluno->id;
            } else {
                $ids_cpf_invalido[] = $aluno->id;
            }
        }

        foreach (array_chunk($ids_cpf_valido, $chunkSize) as $idsChunk) {
            Aluno::whereIn('id', $idsChunk)->update(['cpf_valido' => 1]);
            $bar->advance(count($idsChunk));
        }

        foreach (array_chunk($ids_cpf_invalido, $chunkSize) as $idsChunk) {
            Aluno::whereIn('id', $idsChunk)->update(['cpf_valido' => 0]);
            $bar->advance(count($idsChunk));
        }

        $bar->finish();
        $this->newLine();
        $validos = count($ids_cpf_valido);
        $invalidos = count($ids_cpf_invalido);

        $this->info("Atualizados {$validos} CPF's válidos e {$invalidos} CPF's inválidos.");
    }

    protected function generateReport($totalDuplicates, $updatedCount)
    {
        $this->info('Gerando relatório estatístico...');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set report headers
        $sheet->setCellValue('A1', 'Relatório de Atualização de Registros Únicos');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add report data
        $sheet->setCellValue('A3', 'Data do Processamento:');
        $sheet->setCellValue('B3', now()->format('d/m/Y H:i:s'));

        $sheet->setCellValue('A4', 'Total de Códigos INEP com Duplicatas:');
        $sheet->setCellValue('B4', $totalDuplicates);

        $sheet->setCellValue('A5', 'Registros Atualizados:');
        $sheet->setCellValue('B5', $updatedCount);

        // Style the report
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);
        $sheet->getStyle('A3:B5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(25);

        // Save the report
        $reportPath = storage_path('reports/aluno_censo_update_report_' . now()->format('Ymd_His') . '.xlsx');
        if (!file_exists(dirname($reportPath))) {
            mkdir(dirname($reportPath), 0755, true);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($reportPath);

        $this->info("Relatório gerado em: {$reportPath}");
    }
}
