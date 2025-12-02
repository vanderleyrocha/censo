<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\StringService;

class ProcessSqlFile extends Command
{
    protected $signature = 'sql:process-file {file : Caminho do arquivo SQL} {--chunk-size=100 : Quantidade de INSERTs por transação}';
    protected $description = 'Processa arquivo SQL extraindo e executando comandos INSERT INTO alunos';

    protected StringService $stringService;

    public function __construct(StringService $stringService)
    {
        parent::__construct();
        $this->stringService = $stringService;
    }

    private $totalBlocks = 0;
    private $processedBlocks = 0;
    private $startTime;
    private $currentFilePosition = 0;
    private $fileSize = 0;

    public function handle()
    {
        $filePath = $this->argument('file');
        $chunkSize = (int)$this->option('chunk-size');

        if (!file_exists($filePath)) {
            $this->error("Arquivo não encontrado: {$filePath}");
            return 1;
        }

        $this->fileSize = filesize($filePath);
        $this->startTime = microtime(true);

        $this->info("Iniciando processamento do arquivo: {$filePath}");
        $this->info("Tamanho do arquivo: " . $this->formatBytes($this->fileSize));
        $this->info("Chunk size: {$chunkSize} INSERTs por transação");

        // Primeira passada: contar total de blocos
        $this->info("\nContando total de comandos INSERT...");
        $this->countInsertBlocks($filePath);

        if ($this->totalBlocks === 0) {
            $this->warn("Nenhum comando INSERT INTO alunos encontrado no arquivo.");
            return 0;
        }

        $this->info("Total de comandos INSERT encontrados: {$this->totalBlocks}");

        // Segunda passada: processar e executar
        $this->info("\nIniciando execução dos comandos...");
        $this->processSqlFile($filePath, $chunkSize);

        $totalTime = microtime(true) - $this->startTime;
        $this->info("\nProcessamento concluído!");
        $this->info("Tempo total: " . number_format($totalTime, 2) . " segundos");
        $this->info("Comandos executados: {$this->processedBlocks}/{$this->totalBlocks}");

        return 0;
    }

    private function countInsertBlocks($filePath)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Não foi possível abrir o arquivo para leitura");
        }

        $buffer = '';
        $inInsert = false;

        while (!feof($handle)) {
            $buffer .= fread($handle, 8192); // 8KB chunks para leitura
            $lines = explode("\n", $buffer);

            // Mantém a última linha incompleta no buffer
            $buffer = array_pop($lines);

            foreach ($lines as $line) {
                $trimmedLine = trim($line);

                if (str_starts_with($trimmedLine, 'INSERT INTO')) {
                    $inInsert = true;
                }

                if ($inInsert && str_contains($trimmedLine, ';')) {
                    $this->totalBlocks++;
                    $inInsert = false;
                }
            }
        }

        // Processa o buffer final
        if ($inInsert && str_contains($buffer, ';')) {
            $this->totalBlocks++;
        }

        fclose($handle);
    }

    private function processSqlFile($filePath, $chunkSize)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Não foi possível abrir o arquivo para leitura");
        }

        $progressBar = $this->output->createProgressBar($this->totalBlocks);
        $progressBar->setFormat("%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%");

        $currentChunk = [];
        $buffer = '';
        $inInsert = false;
        $currentInsert = '';

        while (!feof($handle)) {
            $this->currentFilePosition = ftell($handle);
            $buffer .= fread($handle, 16384); // 16KB chunks para melhor performance

            $lines = explode("\n", $buffer);
            $buffer = array_pop($lines); // Mantém linha incompleta

            foreach ($lines as $line) {
                $trimmedLine = trim($line);

                // Detecta início do INSERT
                if (str_starts_with($trimmedLine, 'INSERT INTO')) {
                    $inInsert = true;
                    $currentInsert = $trimmedLine;
                } elseif ($inInsert) {
                    $currentInsert .= ' ' . $trimmedLine;
                }

                // Detecta fim do INSERT (ponto e vírgula)
                if ($inInsert && str_contains($trimmedLine, ';')) {
                    // Processa a query para corrigir sequências problemáticas
                    $processedQuery = $this->stringService->replacesBackslashWithApostrophe($currentInsert);
                    $currentChunk[] = $processedQuery;
                    $inInsert = false;
                    $currentInsert = '';

                    // Executa quando atingir o chunk size
                    if (count($currentChunk) >= $chunkSize) {
                        $this->executeChunk($currentChunk);
                        $progressBar->advance(count($currentChunk));
                        $currentChunk = [];
                    }
                }
            }

            // Atualiza informações de progresso a cada 1000 linhas processadas
            if ($this->processedBlocks % 1000 === 0) {
                $progressBar->setMessage($this->getProgressInfo(), 'status');
            }
        }

        // Processa buffer final e chunk restante
        if ($inInsert && str_contains($buffer, ';')) {
            $processedQuery = $this->stringService->replacesBackslashWithApostrophe($currentInsert . ' ' . $buffer);
            $currentChunk[] = $processedQuery;
        }

        if (!empty($currentChunk)) {
            $this->executeChunk($currentChunk);
            $progressBar->advance(count($currentChunk));
        }

        $progressBar->finish();
        fclose($handle);
    }


    private function executeChunk(array $queries)
    {
        try {
            DB::beginTransaction();
            DB::statement("SET FOREIGN_KEY_CHECKS=0;");
            foreach ($queries as $query) {
                // Remove possíveis caracteres problemáticos
                $query = trim($query);
                if (empty($query)) continue;

                DB::statement($query);
                $this->processedBlocks++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error("\nErro ao executar lote: " . $e->getMessage());
            $this->warn("Comandos executados com sucesso até agora: {$this->processedBlocks}");

            // Log do erro para debugging
            Log::error('Erro ao processar SQL de alunos', [
                'error' => $e->getMessage(),
                'processed_blocks' => $this->processedBlocks,
                'query_sample' => $queries[0] ?? 'N/A'
            ]);

            throw $e;
        } finally {
            DB::statement("SET FOREIGN_KEY_CHECKS=1;");
        }
    }

    private function getProgressInfo()
    {
        $elapsed = microtime(true) - $this->startTime;
        $progress = $this->totalBlocks > 0 ? $this->processedBlocks / $this->totalBlocks : 0;
        $estimated = $progress > 0 ? $elapsed / $progress : 0;
        $remaining = $estimated - $elapsed;

        $fileProgress = $this->fileSize > 0 ?
            number_format(($this->currentFilePosition / $this->fileSize) * 100, 1) : 0;

        return sprintf(
            "Progresso: %d/%d (%.1f%%) | Arquivo: %.1f%% | Tempo: %s/%s",
            $this->processedBlocks,
            $this->totalBlocks,
            $progress * 100,
            $fileProgress,
            $this->formatTime($elapsed),
            $this->formatTime($estimated)
        );
    }

    private function formatTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        }

        return sprintf("%02d:%02d", $minutes, $seconds);
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
