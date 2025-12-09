<?php

namespace App\Console\Commands;

use App\Utils\Validate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AlunoCensoUpdate extends Command
{
    protected $signature = 'aluno-censo:update
        {--registro-unico : Atualiza o campo registro_unico}
        {--valida-cpf : Verifica se o CPF é válido}
        {--nulos : Verifica campos com valores vazios ou -- e substitui por NULL}
    ';

    protected $description = 'Rotinas de atualização na tabela alunos_censo_2024';

    protected $modelAluno;

    protected $tableName;

    public function __construct()
    {
        parent::__construct();

        $this->modelAluno = \App\Models\Aluno2025::class;
        $this->tableName = (new $this->modelAluno)->getTable();
    }

    public function handle()
    {
        $this->info('Iniciando processamento com base nos parâmetros passados...');
        $this->newLine();

        $registroUnico = (bool) $this->option('registro-unico');
        $validaCpf     = (bool) $this->option('valida-cpf');
        $tratarNulos   = (bool) $this->option('nulos');

        if (! $registroUnico && ! $validaCpf && ! $tratarNulos) {
            $this->warn('Nenhuma opção foi informada. Use --registro-unico, --valida-cpf ou --nulos.');
            return Command::INVALID;
        }

        // Ordem pensada para combinar flags:
        // 1) Limpa nulos
        // 2) Valida CPF
        // 3) Trata registro único
        if ($tratarNulos) {
            $this->runTratarNulos();
            $this->newLine();
        }

        if ($validaCpf) {
            $this->runValidaCpf();
            $this->newLine();
        }

        if ($registroUnico) {
            $this->runRegistroUnico();
            $this->newLine();
        }

        $this->info('Processo concluído com sucesso!');
        return Command::SUCCESS;
    }

    /* =========================================================================
     *  BLOCO: REGISTRO ÚNICO
     * ========================================================================= */

    protected function runRegistroUnico(): void
    {
        $this->info('Iniciando processo de atualização de registros únicos...');
        $this->line('Resetando campo registro_unico para 0 em todos os registros...');

        // Reset geral em uma única query
        $resetCount = $this->modelAluno::query()->update(['registro_unico' => 0]);
        $this->line("Registros afetados no reset: {$resetCount}");

        // Step 1: Atualiza registros com cod_inep_aluno único
        $this->updateUniqueRecords();

        // Step 2: Processa duplicatas com prioridade
        $this->processDuplicates();
    }

    protected function updateUniqueRecords(): void
    {
        $this->info('Atualizando registros com cod_inep_aluno único...');

        // cod_inep_aluno que aparece uma única vez
        $uniqueCodes = $this->modelAluno::query()->select('cod_inep_aluno')->groupBy('cod_inep_aluno')->havingRaw('COUNT(*) = 1')->pluck('cod_inep_aluno');

        $totalUnique = $uniqueCodes->count();
        $this->line("Encontrados {$totalUnique} registros com cod_inep_aluno único.");

        if ($totalUnique === 0) {
            $this->warn('Nenhum cod_inep_aluno único encontrado.');
            return;
        }

        $bar = $this->output->createProgressBar($totalUnique);
        $bar->start();

        $chunkSize = 1000;
        $updated = 0;

        $uniqueCodes->chunk($chunkSize)->each(function ($chunk) use ($bar, &$updated) {
            $count = $this->modelAluno::whereIn('cod_inep_aluno', $chunk)
                ->update(['registro_unico' => 1]);

            $updated += $count;
            $bar->advance($chunk->count());
        });

        $bar->finish();
        $this->newLine();
        $this->info("Atualizados {$updated} registros com cod_inep_aluno único.");
    }

    protected function processDuplicates(): void
    {
        $this->info('Processando registros duplicados (com prioridade)...');
        $startTime = microtime(true);

        $duplicateCodes = $this->modelAluno::query()
            ->select('cod_inep_aluno')
            ->groupBy('cod_inep_aluno')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('cod_inep_aluno');

        $totalDuplicates = $duplicateCodes->count();
        $this->line("Encontrados {$totalDuplicates} códigos INEP com registros duplicados.");

        if ($totalDuplicates === 0) {
            $this->warn('Nenhum código INEP duplicado encontrado.');
            return;
        }

        $batchSize    = 500;
        $processed    = 0;
        $updatedCount = 0;

        $bar = $this->output->createProgressBar($totalDuplicates);
        $bar->setFormat("%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%\nProcessando: %message%");
        $bar->setMessage('Iniciando...');
        $bar->start();

        $duplicateCodes->chunk($batchSize)->each(function ($batch) use ($bar, &$processed, &$updatedCount, $totalDuplicates) {
            // IDs que devem receber registro_unico = 1 (maior prioridade, depois maior id)
            $recordsToUpdate = $this->modelAluno::query()
                ->whereIn('cod_inep_aluno', $batch)
                ->selectRaw('id')
                ->whereRaw("
                    id = (
                        SELECT id FROM {$this->tableName} AS sub
                        WHERE {$this->tableName}.cod_inep_aluno = sub.cod_inep_aluno
                        ORDER BY prioridade DESC, id DESC
                        LIMIT 1
                    )
                ")
                ->pluck('id')
                ->toArray();

            if (!empty($recordsToUpdate)) {
                $count = $this->modelAluno::whereIn('id', $recordsToUpdate)->update(['registro_unico' => 1]);
                $updatedCount += $count;
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

        $this->generateReport($totalDuplicates, $updatedCount);
    }

    /* =========================================================================
     *  BLOCO: VALIDAÇÃO DE CPF
     * ========================================================================= */

    protected function runValidaCpf(): void
    {
        $this->info('Iniciando validação de CPF...');

        $total = $this->modelAluno::count();
        if ($total === 0) {
            $this->warn('Nenhum registro encontrado para validação de CPF.');
            return;
        }

        $this->line("Encontrados {$total} registros.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $chunkSize = 2000;
        $totalValidos   = 0;
        $totalInvalidos = 0;

        $this->modelAluno::select(['id', 'cpf'])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($alunos) use (&$totalValidos, &$totalInvalidos, $bar) {
                $idsCpfValido   = [];
                $idsCpfInvalido = [];

                foreach ($alunos as $aluno) {
                    if (Validate::cpf($aluno->cpf)) {
                        $idsCpfValido[] = $aluno->id;
                    } else {
                        $idsCpfInvalido[] = $aluno->id;
                    }
                }

                if (! empty($idsCpfValido)) {
                    $count = $this->modelAluno::whereIn('id', $idsCpfValido)
                        ->update(['cpf_valido' => 1]);
                    $totalValidos += $count;
                }

                if (! empty($idsCpfInvalido)) {
                    $count = $this->modelAluno::whereIn('id', $idsCpfInvalido)
                        ->update(['cpf_valido' => 0]);
                    $totalInvalidos += $count;
                }

                $bar->advance($alunos->count());
            });

        $bar->finish();
        $this->newLine();

        $this->info("CPF's válidos atualizados: {$totalValidos}");
        $this->info("CPF's inválidos atualizados: {$totalInvalidos}");
    }

    /* =========================================================================
     *  BLOCO: TRATAMENTO DE NULOS
     * ========================================================================= */

    protected function runTratarNulos(): void
    {
        $this->info('Iniciando tratamento de campos com valores vazios ou "--" (modo otimizado por coluna)...');

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model      = new $this->modelAluno;
        $table      = $model->getTable();
        $connection = $model->getConnection();

        // Lista todas as colunas da tabela
        $allColumns = Schema::getColumnListing($table);

        // Colunas que não devem ser alteradas
        $ignore = array_filter([
            $model->getKeyName(),
            $model::CREATED_AT ?? null,
            $model::UPDATED_AT ?? null,
            'cod_inep_escola',
            'nome_escola',
            'municipio',
            'dependencia_administrativa',
            'prioridade',
            'registro_unico',
            'cpf_valido',
        ]);

        $textColumns = [];

        // Selecionar somente campos VARCHAR/TEXT
        $columnsInfo = DB::select("SHOW COLUMNS FROM {$table}");

        foreach ($columnsInfo as $col) {

            $columnName = $col->Field;
            $type       = strtolower($col->Type);

            if (in_array($columnName, $ignore, true)) {
                continue;
            }

            // Apenas VARCHAR, CHAR, TEXT
            if (str_starts_with($type, 'varchar') || str_starts_with($type, 'char') || str_contains($type, 'text')) {
                $textColumns[] = $columnName;
            }
        }
        if (empty($textColumns)) {
            $this->warn('Nenhuma coluna de texto encontrada para tratamento.');
            return;
        }

        $this->info('Colunas de texto a serem tratadas:');
        $this->line(implode(', ', $textColumns));

        $startTime = microtime(true);
        $totalAffectedRows = 0;

        $bar = $this->output->createProgressBar(count($textColumns));
        $bar->start();

        foreach ($textColumns as $column) {
            $affected = DB::table($table)
                ->where(function ($query) use ($column) {
                    $query->where($column, '')
                        ->orWhere($column, '--');
                })
                ->update([$column => null]);

            $totalAffectedRows += $affected;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $executionTime = round(microtime(true) - $startTime, 2);

        $this->info("Tratamento de nulos concluído.");
        $this->info('Colunas processadas: ' . count($textColumns));
        $this->info("Registros afetados (somando todas as colunas): {$totalAffectedRows}");
        $this->info("Tempo total: {$executionTime} segundos.");
    }

    /* =========================================================================
     *  BLOCO: RELATÓRIO
     * ========================================================================= */

    protected function generateReport(int $totalDuplicates, int $updatedCount): void
    {
        $this->info('Gerando relatório estatístico...');

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Cabeçalho
        $sheet->setCellValue('A1', 'Relatório de Atualização de Registros Únicos');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Dados
        $sheet->setCellValue('A3', 'Data do Processamento:');
        $sheet->setCellValue('B3', Carbon::now()->format('d/m/Y H:i:s'));

        $sheet->setCellValue('A4', 'Total de Códigos INEP com Duplicatas:');
        $sheet->setCellValue('B4', $totalDuplicates);

        $sheet->setCellValue('A5', 'Registros Atualizados (marcados como registro_unico):');
        $sheet->setCellValue('B5', $updatedCount);

        // Estilo
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);
        $sheet->getStyle('A3:B5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getColumnDimension('A')->setWidth(45);
        $sheet->getColumnDimension('B')->setWidth(25);

        $reportPath = storage_path(
            'reports/aluno_censo_update_report_' . Carbon::now()->format('Ymd_His') . '.xlsx'
        );

        if (! file_exists(dirname($reportPath))) {
            mkdir(dirname($reportPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($reportPath);

        $this->info("Relatório gerado em: {$reportPath}");
    }
}
