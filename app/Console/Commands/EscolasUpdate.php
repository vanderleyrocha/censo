<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use App\Models\Escola;

class EscolasUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escola:update {filename : Path to the Excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update records based on IDs from an Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('filename');

        if (!file_exists($filename)) {
            $this->error("O arquivo {$filename} não foi encontrado.");
            return Command::FAILURE;
        }

        try {
            // Configurar o reader para modo de leitura eficiente
            $reader = $this->getReader($filename);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);

            // Carregar apenas os dados necessários
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            // Obter os dados das linhas
            $rows = $worksheet->toArray();
            
            // Remover cabeçalho se existir
            $header = array_shift($rows);
            
            // Verificar se as colunas esperadas existem
            $expectedColumns = ['cidade', 'dependencia', 'zona', 'id', 'nome'];
            $headerLower = array_map('strtolower', $header);
            
            foreach ($expectedColumns as $expected) {
                if (!in_array($expected, $headerLower)) {
                    $this->error("A coluna '{$expected}' não foi encontrada no arquivo Excel.");
                    return Command::FAILURE;
                }
            }
            
            // Mapear índices das colunas
            $columnIndexes = [
                'cidade' => array_search('cidade', $headerLower),
                'dependencia' => array_search('dependencia', $headerLower),
                'zona' => array_search('zona', $headerLower),
                'id' => array_search('id', $headerLower),
                'nome' => array_search('nome', $headerLower),
            ];

            // Extrair IDs e preparar para atualização
            $ids = [];
            $totalRows = count($rows);
            
            $this->info("Processando {$totalRows} registros...");
            $progressBar = $this->output->createProgressBar($totalRows);
            $progressBar->start();

            foreach ($rows as $row) {
                if (!empty($row[$columnIndexes['id']])) {
                    $ids[] = $row[$columnIndexes['id']];
                }
                $progressBar->advance();
                
                // Liberar memória periodicamente
                if (count($ids) % 1000 === 0) {
                    gc_collect_cycles();
                }
            }

            $progressBar->finish();
            $this->newLine(2); // Espaço após a barra de progresso

            $this->info(count($ids) . " IDs válidos encontrados no arquivo Excel.");

            if (empty($ids)) {
                $this->warn("Nenhum ID válido encontrado no arquivo.");
                return Command::SUCCESS;
            }

            // Atualizar em chunks para evitar problemas de memória
            $chunkSize = 1000;
            $totalUpdated = 0;
            $progressBar = $this->output->createProgressBar(count(array_chunk($ids, $chunkSize)));
            $this->info("Atualizando registros no banco de dados...");
            $progressBar->start();

            foreach (array_chunk($ids, $chunkSize) as $chunk) {
                $updated = Escola::whereIn('id', $chunk)
                    ->update(['situacao' => 'Funcionando em 2024']);
                $totalUpdated += $updated;
                $progressBar->advance();
                
                // Liberar memória
                gc_collect_cycles();
            }

            $progressBar->finish();
            $this->newLine(2); // Espaço após a barra de progresso

            $this->info("{$totalUpdated} registros atualizados com sucesso.");

            // Liberar memória explicitamente
            unset($spreadsheet, $worksheet, $rows);
            gc_collect_cycles();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Erro ao processar o arquivo: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get the appropriate reader for the file type.
     */
    protected function getReader(string $filename): IReader
    {
        return IOFactory::createReaderForFile($filename);
    }
}