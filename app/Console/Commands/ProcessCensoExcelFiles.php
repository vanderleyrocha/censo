<?php

namespace App\Console\Commands;

use App\Helpers\Format;
use App\Models\Aluno;
use App\Models\Escola;
use \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Helper\ProgressBar;

class ProcessCensoExcelFiles extends Command
{
    protected $signature = 'censo:process 
                            {directory : The directory containing Excel files to process}
                            {--F|force : Force reprocessing of already processed schools}';

    protected $description = 'Process Excel files from Censo escolar recursively';

    protected ProgressBar $progressBar;

    protected $cidadesCache = [];

    public function handle()
    {
        $directory = $this->argument('directory');
        $force = $this->option('force');

        if (!is_dir($directory)) {
            $this->error("Directory {$directory} does not exist.");
            return Command::FAILURE;
        }

        $files = $this->findExcelFiles($directory);
        $totalFiles = count($files);

        if ($totalFiles === 0) {
            $this->info('No Excel files found in the specified directory.');
            return Command::SUCCESS;
        }

        $this->info("Found {$totalFiles} Excel files to process.");
        $this->newLine();

        $this->progressBar = $this->output->createProgressBar($totalFiles);
        $this->progressBar->setFormat(
            "%current%/%max% [%bar%] %percent:3s%%\n" .
                "Elapsed: %elapsed:6s%\n" .
                "Remaining: %remaining:6s%\n" .
                "Memory: %memory:6s%\n" .
                "Processing: %message%"
        );

        $stats = [
            'processed' => 0,
            'skipped' => 0,
            'failed' => 0,
            'students_added' => 0,
            'schools_added' => 0,
        ];

        foreach ($files as $file) {
            $this->progressBar->setMessage(basename($file));
            $this->processFile($file, $force, $stats);
            $this->progressBar->advance();
        }

        $this->progressBar->finish();
        $this->newLine(2);

        $this->info('Processing completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Files', $totalFiles],
                ['Processed Files', $stats['processed']],
                ['Skipped Files', $stats['skipped']],
                ['Failed Files', $stats['failed']],
                ['New Schools Added', $stats['schools_added']],
                ['New Students Added', $stats['students_added']],
            ]
        );

        return Command::SUCCESS;
    }

    protected function findExcelFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isDir() && in_array($file->getExtension(), ['xlsx', 'xls'])) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    protected function processFile(string $filePath, bool $force, array &$stats): void
    {
        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $escolaData = $this->extractSchoolData($worksheet);

            if (!$escolaData || !$escolaData['escola_id']) {
                $stats['skipped']++;
                return;
            }

            $escola = Escola::firstOrNew(['id' => $escolaData['escola_id']]);

            if (!$force && $escola->exists && $escola->atualizado) {
                $stats['skipped']++;
                return;
            }

            DB::transaction(function () use ($escolaData, $escola, $worksheet, &$stats) {
                if (!$escola->exists) {
                    $escola->fill([
                        'atualizado' => true,
                    ])->save();
                    $stats['schools_added']++;
                }

                // Check if this is a general list file (without class info)
                $isGeneralList = $this->isGeneralListFile($worksheet, $escolaData['highest_row']);

                if ($isGeneralList) {
                    $turmasData = $this->extractGeneralListData($worksheet, $escolaData);
                } else {
                    $turmasData = $this->extractClassData($worksheet, $escolaData);
                }

                $alunosParaInserir = [];

                foreach ($turmasData as $turmaData) {
                    foreach ($turmaData['alunos'] as $alunoData) {
                        $alunosParaInserir[] = $alunoData;
                    }
                }

                if (!empty($alunosParaInserir)) {
                    $codigosAlunos = array_column($alunosParaInserir, 'cod_inep_aluno');
                    $codigosTurmas = array_column($alunosParaInserir, 'cod_turma');

                    $existentes = Aluno::whereIn('cod_inep_aluno', $codigosAlunos)
                        ->whereIn('cod_turma', $codigosTurmas)
                        ->pluck('cod_inep_aluno', 'cod_turma')
                        ->toArray();

                    $alunosParaInserir = array_filter($alunosParaInserir, function ($aluno) use ($existentes) {
                        return !isset($existentes[$aluno['cod_turma']]) ||
                            $existentes[$aluno['cod_turma']] != $aluno['cod_inep_aluno'];
                    });

                    if (!empty($alunosParaInserir)) {
                        $chunks = array_chunk($alunosParaInserir, 500);
                        foreach ($chunks as $chunk) {
                            Aluno::insert($chunk);
                        }
                        $stats['students_added'] += count($alunosParaInserir);
                    }
                }

                if (!$escola->atualizado) {
                    $escola->update(['atualizado' => true]);
                }

                $stats['processed']++;
            });
        } catch (\Throwable $e) {
            Log::error("Error processing file {$filePath}: {$e->getMessage()}");
            $stats['failed']++;
        } finally {
            if (isset($spreadsheet)) {
                $worksheet->disconnectCells();
                $spreadsheet->disconnectWorksheets();
                unset($worksheet, $spreadsheet);
            }
            gc_collect_cycles();
        }
    }

    protected function isGeneralListFile(Worksheet $worksheet, int $highestRow): bool
    {
        // Check if this is a general list file (without class info)
        $hasTurmaInfo = $this->findCellRow($worksheet, 1, 'A', 'Informações da Turma', $highestRow);
        return !$hasTurmaInfo;
    }

    protected function extractGeneralListData(Worksheet $worksheet, array $escolaData): array
    {
        $turmaData = [
            'codigo' => null,
            'nome' => null,
            'mediacao_pedagogica' => null,
            'atendimento' => null,
            'estrutura_curricular' => null,
            'local_funcionamento' => null,
            'dias_semana' => null,
            'horario_atendimento' => null,
            'modalidade' => null,
            'etapa' => null,
            'organizacao' => null,
            'libras' => null,
            'alunos' => [],
        ];

        $studentsStart = $this->findCellRow($worksheet, 1, 'A', 'Ordem', $escolaData['highest_row']);
        if (!$studentsStart) {
            return [$turmaData];
        }

        $studentsStart++;
        $currentRow = $studentsStart;

        while ($currentRow <= $escolaData['highest_row'] && is_numeric($worksheet->getCell("A{$currentRow}")->getValue())) {
            $codInepAluno = intval($worksheet->getCell("B{$currentRow}")->getValue());
            if ($codInepAluno > 0) {
                $turmaData['alunos'][] = [
                    'ano_censo' => 2024,
                    'cod_inep_escola' => $escolaData['escola_id'],
                    'escola' => $escolaData['escola_nome'],
                    'municipio' => $escolaData['escola_municipio'],
                    'uf' => $escolaData['escola_uf'],
                    'localizacao' => $escolaData['escola_localizacao'],
                    'dependencia' => $escolaData['escola_dependencia'],
                    'cod_turma' => null, // No class info in general list
                    'nome_turma' => null,
                    'tipo_mediacao' => null,
                    'tipo_atendimento' => null,
                    'estrutura_curricular' => null,
                    'local_funcionamento_turma' => null,
                    'dias_semana' => null,
                    'horario' => null,
                    'modalidade' => null,
                    'etapa' => null,
                    'forma_organizacao' => null,
                    'libras' => 0,
                    'cod_inep_aluno' => $codInepAluno,
                    'nome' => $worksheet->getCell("C{$currentRow}")->getValue(),
                    'dt_nascimento' => Format::dateBRtoEn($worksheet->getCell("D{$currentRow}")->getValue()),
                    'cor' => $worksheet->getCell("E{$currentRow}")->getValue(),
                    'sexo' => $worksheet->getCell("F{$currentRow}")->getValue(),
                    'deficiencia' => NULL,
                    'recursos' => NULL,
                    'cpf' => Format::digitOnly($worksheet->getCell("G{$currentRow}")->getValue()),
                ];
            }
            $currentRow++;
        }

        return [$turmaData];
    }

    protected function extractSchoolData(Worksheet $worksheet): ?array
    {
        $highestRow = $worksheet->getHighestRow();

        // Try to find school info by looking for "Código da escola:"
        $linha = $this->findCellRow($worksheet, 1, 'A', 'Código da escola:', $highestRow);
        if (!$linha) {
            return null;
        }

        $escolaId = $worksheet->getCell("B{$linha}")->getValue();
        if (!is_numeric($escolaId)) {
            return null;
        }

        $linha++;
        return [
            'escola_id' => $escolaId,
            'escola_nome' => $worksheet->getCell("B{$linha}")->getValue(),
            'escola_uf' => $worksheet->getCell("B" . ($linha + 1))->getValue(),
            'escola_municipio' => $worksheet->getCell("B" . ($linha + 2))->getValue(),
            'escola_localizacao' => $worksheet->getCell("B" . ($linha + 3))->getValue(),
            'escola_dependencia' => $worksheet->getCell("B" . ($linha + 4))->getValue(),
            'current_row' => $linha + 5,
            'highest_row' => $highestRow,
        ];
    }

    protected function extractClassData(Worksheet $worksheet, array $escolaData): array
    {
        $turmas = [];
        $currentRow = $escolaData['current_row'];

        while ($currentRow <= $escolaData['highest_row']) {
            $turmaStart = $this->findCellRow($worksheet, $currentRow, 'A', 'Código da turma:', $escolaData['highest_row']);
            if (!$turmaStart) {
                break;
            }

            $turmaData = [
                'codigo' => $worksheet->getCell("B{$turmaStart}")->getValue(),
                'nome' => $worksheet->getCell("B" . ($turmaStart + 1))->getValue(),
                'mediacao_pedagogica' => $worksheet->getCell("B" . ($turmaStart + 2))->getValue(),
                'atendimento' => $worksheet->getCell("B" . ($turmaStart + 3))->getValue(),
                'estrutura_curricular' => $worksheet->getCell("B" . ($turmaStart + 4))->getValue(),
                'local_funcionamento' => $worksheet->getCell("B" . ($turmaStart + 5))->getValue(),
                'dias_semana' => $worksheet->getCell("B" . ($turmaStart + 6))->getValue(),
                'horario_atendimento' => $worksheet->getCell("B" . ($turmaStart + 7))->getValue(),
                'modalidade' => $worksheet->getCell("B" . ($turmaStart + 8))->getValue(),
                'etapa' => $worksheet->getCell("B" . ($turmaStart + 9))->getValue(),
                'organizacao' => $worksheet->getCell("B" . ($turmaStart + 10))->getValue(),
                'libras' => $worksheet->getCell("B" . ($turmaStart + 11))->getValue(),
                'alunos' => [],
            ];

            $studentsStart = $this->findCellRow($worksheet, $turmaStart + 12, 'A', 'Ordem', $escolaData['highest_row']);
            if (!$studentsStart) {
                $currentRow = $turmaStart + 13;
                continue;
            }

            $studentsStart++;
            $currentRow = $studentsStart;

            while ($currentRow <= $escolaData['highest_row'] && is_numeric($worksheet->getCell("A{$currentRow}")->getValue())) {
                $codInepAluno = intval($worksheet->getCell("B{$currentRow}")->getValue());
                if ($codInepAluno > 0) {
                    $turmaData['alunos'][] = [
                        'ano_censo' => 2024,
                        'cod_inep_escola' => $escolaData['escola_id'],
                        'escola' => $escolaData['escola_nome'],
                        'municipio' => $escolaData['escola_municipio'],
                        'uf' => $escolaData['escola_uf'],
                        'localizacao' => $escolaData['escola_localizacao'],
                        'dependencia' => $escolaData['escola_dependencia'],
                        'cod_turma' => $turmaData['codigo'],
                        'nome_turma' => $turmaData['nome'],
                        'tipo_mediacao' => $turmaData['mediacao_pedagogica'],
                        'tipo_atendimento' => $turmaData['atendimento'],
                        'estrutura_curricular' => $turmaData['estrutura_curricular'],
                        'local_funcionamento_turma' => $turmaData['local_funcionamento'],
                        'dias_semana' => $turmaData['dias_semana'],
                        'horario' => $turmaData['horario_atendimento'],
                        'modalidade' => $turmaData['modalidade'],
                        'etapa' => $turmaData['etapa'],
                        'forma_organizacao' => $turmaData['organizacao'],
                        'libras' => ($turmaData['libras'] == "Sim" ? 1 : 0),
                        'cod_inep_aluno' => $codInepAluno,
                        'nome' => $worksheet->getCell("C{$currentRow}")->getValue(),
                        'dt_nascimento' => Format::dateBRtoEn($worksheet->getCell("D{$currentRow}")->getValue()),
                        'cor' => $worksheet->getCell("E{$currentRow}")->getValue(),
                        'sexo' => $worksheet->getCell("F{$currentRow}")->getValue(),
                        'deficiencia' => $worksheet->getCell("G{$currentRow}")->getValue(),
                        'recursos' => $worksheet->getCell("H{$currentRow}")->getValue(),
                        'cpf' => Format::digitOnly($worksheet->getCell("I{$currentRow}")->getValue()),
                    ];
                }
                $currentRow++;
            }

            $turmas[] = $turmaData;
        }

        return $turmas;
    }

    protected function findCellRow(Worksheet $worksheet, int $startRow, string $column, string $searchText, int $maxRow, int $maxSearch = 100): ?int 
    {
        $currentRow = $startRow;
        $endRow = min($startRow + $maxSearch, $maxRow);

        while ($currentRow <= $endRow) {
            $cellValue = $worksheet->getCell("{$column}{$currentRow}")->getValue();
            if ($cellValue == $searchText) {
                return $currentRow;
            }
            $currentRow++;
        }

        return null;
    }
}