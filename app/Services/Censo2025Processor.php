<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Carbon\Carbon;

class Censo2025Processor
{
    private Censo2025Validator $validator;
    private Censo2025DataMapper $dataMapper;
    private Censo2025Repository $repository;
    private Censo2025FileHandler $fileHandler;
    private ?ProgressBar $progressBar = null;
    private ?OutputInterface $output = null;
    private int $chunkSize = 300; // Valor padrão
    private array $successfulSchools = []; // Array para armazenar escolas processadas com sucesso
    private $startTime;
    private $processedFiles = 0;

    public function __construct(
        Censo2025Validator $validator,
        Censo2025DataMapper $dataMapper,
        Censo2025Repository $repository,
        Censo2025FileHandler $fileHandler
    ) {
        $this->validator = $validator;
        $this->dataMapper = $dataMapper;
        $this->repository = $repository;
        $this->fileHandler = $fileHandler;
    }

    public function setChunkSize(int $chunkSize): void
    {
        $this->chunkSize = $chunkSize;
    }

    public function setProgressBar(ProgressBar $progressBar): void
    {
        $this->progressBar = $progressBar;
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getSuccessfulSchools(): array
    {
        return $this->successfulSchools;
    }


    public function processAllFiles(): array
    {
        Log::info("executando método Censo2025Processor@processAllFiles");
        $files = $this->fileHandler->getFilesToProcess();
        $totalFiles = count($files);

        if ($this->output) {
            $this->output->writeln("<info>Total de arquivos encontrados para processamento: {$totalFiles}</info>");
            $this->output->writeln("<info>Tamanho do chunk: {$this->chunkSize} registros</info>");
        }

        Log::info("Total de arquivos encontrados para processamento: {$totalFiles}");
        Log::info("Tamanho do chunk definido: {$this->chunkSize}");

        $this->startTime = microtime(true);
        $this->processedFiles = 0;
        $this->initializeProgress($totalFiles);

        $results = [
            'processed' => 0,
            'errors' => 0,
            'records' => 0,
            'reports' => [],
            'successful_schools' => []
        ];

        // MODIFICAÇÃO: Processar em lotes menores para liberar memória
        $batchSize = 50; // Processar 50 arquivos por vez
        $batches = array_chunk($files, $batchSize);

        foreach ($batches as $batchIndex => $batchFiles) {
            $this->output->writeln("<info>Processando lote " . ($batchIndex + 1) . " de " . count($batches) . "</info>");

            foreach ($batchFiles as $key => $file) {
                $result = $this->processSingleFile($file);

                if ($result['success']) {
                    $results['processed']++;
                    $results['records'] += $result['records'];

                    if (isset($result['school_info'])) {
                        $results['successful_schools'][] = $result['school_info'];
                        $this->successfulSchools[] = $result['school_info'];
                    }
                } else {
                    $results['errors']++;
                    $results['reports'][] = $result['report'];
                }

                $this->processedFiles++;
                $this->advanceProgress($totalFiles);

                // MODIFICAÇÃO: Limpar memória após cada arquivo
                $this->cleanupMemory();
            }

            // MODIFICAÇÃO: Forçar coleta de lixo entre lotes
            $this->forceGarbageCollection();
        }

        $this->finishProgress();

        if ($this->output) {
            $totalTime = round(microtime(true) - $this->startTime, 2);
            $this->output->writeln("\n<info>Tempo total de processamento: {$totalTime} segundos</info>");
        }

        return $results;
    }

    private function processSingleFile(string $filePath): array
    {
        try {
            // MODIFICAÇÃO: Usar leitura mais eficiente
            $spreadsheet = $this->fileHandler->loadSpreadsheet($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Validar estrutura básica
            $validationResult = $this->validator->validateStructure($worksheet);
            if (!$validationResult['valid']) {
                return $this->createErrorResult($filePath, $validationResult['errors']);
            }

            // Extrair dados da escola
            $schoolData = $this->dataMapper->extractSchoolData($worksheet);

            // Processar cidade
            $city = $this->repository->findOrCreateCity($schoolData['municipio']);
            if (!$city) {
                return $this->createErrorResult($filePath, ['Cidade não encontrada: ' . $schoolData['municipio']]);
            }

            // Processar escola
            $school = $this->repository->findOrCreateSchool($schoolData, $city->id);

            // MODIFICAÇÃO: Processar alunos em chunks menores
            $studentData = $this->dataMapper->extractStudentData($worksheet, $schoolData);

            if (empty($studentData)) {
                return $this->createErrorResult($filePath, ['Nenhum aluno encontrado para processar']);
            }

            // MODIFICAÇÃO: Usar chunk size menor para inserção
            $processedRecords = $this->repository->bulkInsertStudents($studentData, min($this->chunkSize, 100));

            // MODIFICAÇÃO: Limpar dados dos alunos da memória imediatamente
            unset($studentData);

            // Atualizar estatísticas da escola
            $this->repository->updateSchoolImportStats($school->id, $processedRecords);

            // Mover arquivo processado
            $this->fileHandler->moveProcessedFile($filePath, $schoolData['municipio'], $school->id, $schoolData['nome_escola']);

            // MODIFICAÇÃO: Limpar planilha da memória
            unset($spreadsheet, $worksheet);

            return [
                'success' => true,
                'records' => $processedRecords,
                'school' => $school,
                'school_info' => [
                    'municipio' => $schoolData['municipio'],
                    'nome_escola' => $schoolData['nome_escola'],
                    'cod_inep_escola' => $schoolData['cod_inep_escola'],
                    'registros_importados' => $processedRecords,
                    'nova' => $school->nova,
                    'encontrada' => $school->encontrada
                ]
            ];
        } catch (\Exception $e) {
            $errorMessage = substr($e->getMessage(), 0, 1000) . (strlen($e->getMessage()) > 1000 ? '...' : '');
            \Log::error("Erro ao processar arquivo {$filePath}");
            \Log::error($errorMessage);
            return $this->createErrorResult($filePath, ['Erro durante processamento: ' . $errorMessage]);
        }
    }

    // NOVOS MÉTODOS PARA GERENCIAMENTO DE MEMÓRIA
    private function cleanupMemory(): void
    {
        if (function_exists('gc_mem_caches')) {
            gc_mem_caches();
        }
    }

    private function forceGarbageCollection(): void
    {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        // Limpar cache do Doctrine se estiver sendo usado
        if (class_exists('Doctrine\Common\Cache\Cache')) {
            $em = app('Doctrine\ORM\EntityManager');
            if ($em) {
                $em->clear();
            }
        }
    }

    private function initializeProgress(int $total): void
    {
        if ($this->progressBar) {
            $this->progressBar->setFormat(
                "%current%/%max% [%bar%] %percent:3s%%\n" .
                    "Tempo: %elapsed:6s%/%estimated:-6s%\n" .
                    "Memória: %memory:6s%\n" .
                    "Status: %status%"
            );
            $this->progressBar->setMessage('Iniciando...', 'status');
            $this->progressBar->start($total);
        }
    }

    private function advanceProgress(int $totalFiles): void
    {
        if ($this->progressBar) {
            $elapsed = microtime(true) - $this->startTime;
            $estimatedTotal = ($elapsed / $this->processedFiles) * $totalFiles;
            $remaining = $estimatedTotal - $elapsed;

            $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';

            $status = "Processando arquivo {$this->processedFiles} de {$totalFiles}";

            $this->progressBar->setMessage($memoryUsage, 'memory');
            $this->progressBar->setMessage($status, 'status');
            $this->progressBar->advance();
        }
    }

    private function finishProgress(): void
    {
        if ($this->progressBar) {
            $this->progressBar->setMessage('Concluído!', 'status');
            $this->progressBar->setMessage(round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB', 'memory');
            $this->progressBar->finish();
        }
    }

    private function createErrorResult(string $filePath, array $errors): array
    {
        return [
            'success' => false,
            'records' => 0,
            'report' => [
                'file' => basename($filePath),
                'errors' => $errors,
                'timestamp' => now()
            ]
        ];
    }
}
