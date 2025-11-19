<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Censo2025Processor;
use App\Services\Censo2025ReportGenerator;
use Illuminate\Support\Facades\Log;

class ImportCenso2025Correcoes extends Command
{
    protected $signature = 'censo:process-2025-corrections 
                          {--memory=8192 : Memory limit in MB}
                          {--chunk-size=300 : Number of records to process at once}
                          {--regionais= : IDs das regionais separados por vírgula}';

    protected $description = 'Processa planilhas de correção do Censo Escolar 2025';

    public function handle(Censo2025Processor $processor, Censo2025ReportGenerator $reportGenerator)
    {
        // Configurar ambiente
        $msg = 'Iniciando processo de verificação dos arquivos do censo escolar 2025';
        Log::info($msg);
        $this->info($msg . "\n");
        $this->configureEnvironment();

        // Configurar o tamanho do chunk no processor
        $chunkSize = (int)$this->option('chunk-size');
        $processor->setChunkSize($chunkSize);
        $this->info("Tamanho do chunk definido para: {$chunkSize} registros");

        // Configurar regionais no report generator
        $regionais = $this->option('regionais');
        if ($regionais) {
            $regionaisArray = array_map('trim', explode(',', $regionais));
            $reportGenerator->setRegionais($regionaisArray);
            $this->info("Regionais filtradas: " . implode(', ', $regionaisArray));
        }

        $processor->setProgressBar($this->output->createProgressBar());
        $processor->setOutput($this->output);

        try {
            $result = $processor->processAllFiles();

            // MODIFICAÇÃO: Gerar relatório mesmo quando processed = 0, desde que haja erros
            $hasErrors = !empty($result['errors']) || !empty($result['reports']);
            $hasProcessedFiles = $result['processed'] > 0;

            if ($hasProcessedFiles) {
                $this->info("\nProcessamento concluído!");
                $this->info("Arquivos processados: {$result['processed']}");
                $this->info("Arquivos com erro: {$result['errors']}");
                $this->info("Registros inseridos: {$result['records']}");

                // Gerar relatório incluindo escolas processadas com sucesso
                $reportPath = $reportGenerator->generate($result['reports'], $result['successful_schools']);
                $this->info("Relatório gerado: {$reportPath}");

            } else if ($hasErrors) {
                // MODIFICAÇÃO: Gerar relatório mesmo sem arquivos processados, desde que haja erros
                $this->warn("\nNenhum arquivo foi processado com sucesso, mas foram encontrados erros.");
                $this->info("Arquivos com erro: {$result['errors']}");

                // Gerar relatório apenas com os erros
                $reportPath = $reportGenerator->generate($result['reports'], []);
                $this->info("Relatório de erros gerado: {$reportPath}");
            } else {
                $this->warn("\nNenhum arquivo foi processado e nenhum erro foi encontrado.");
            }

            // NOVA FUNCIONALIDADE: Limpar pastas vazias após o processamento
            $this->info("\nLimpando pastas vazias...");
            $fileHandler = app(\App\Services\Censo2025FileHandler::class);
            $removedFolders = $fileHandler->cleanupEmptyFolders();

            if (!empty($removedFolders)) {
                $this->info("Pastas vazias removidas:");
                foreach ($removedFolders as $folder) {
                    $this->info("  - {$folder}");
                }
            } else {
                $this->info("Nenhuma pasta vazia encontrada para remover.");
            }
        } catch (\Exception $e) {
            $this->error("Erro durante o processamento: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function configureEnvironment(): void
    {
        ini_set('memory_limit', $this->option('memory') . 'M');
        set_time_limit(0);
    }
}