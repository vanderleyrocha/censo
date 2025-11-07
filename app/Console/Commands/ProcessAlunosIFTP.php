<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\Console\Helper\ProgressBar;

class ProcessAlunosIFTP extends Command
{
    protected $signature = 'alunos:processar-iftp-simaed 
                          {--export : Exportar resultados para Excel}
                          {--chunk=1000 : Tamanho do chunk para processamento}';
    
    protected $description = 'Processa alunos IFTP e busca correspondências no SIMAED';
    
    protected $progressBar;
    protected $startTime;
    protected $totalAlunos;
    protected $processed = 0;
    
    public function handle()
    {
        $this->startTime = microtime(true);
        
        $this->info('Iniciando processamento de alunos IFTP...');
        
        // Obter total de alunos para processar
        $this->totalAlunos = DB::table('alunos_iftp')->count();
        
        if ($this->totalAlunos === 0) {
            $this->error('Nenhum aluno encontrado na tabela alunos_iftp');
            return 1;
        }
        
        $this->info("Total de alunos a processar: {$this->totalAlunos}");
        
        // Inicializar barra de progresso
        $this->initializeProgressBar();
        
        // Processar em chunks para otimizar memória
        $this->processAlunosInChunks();
        
        // Finalizar barra de progresso
        $this->finishProgress();
        
        // Exportar resultados se solicitado
        if ($this->option('export')) {
            $this->exportResultsToExcel();
        }
        
        $this->info('Processamento concluído!');
        return 0;
    }
    
    protected function initializeProgressBar(): void
    {
        $this->progressBar = $this->output->createProgressBar($this->totalAlunos);
        $this->progressBar->setFormat(
            "%current%/%max% [%bar%] %percent:3s%%\n".
            "Tempo: %elapsed:6s%/%estimated:-6s%\n".
            "Memória: %memory:6s%\n"
        );
    }
    
    protected function processAlunosInChunks(): void
    {
        $chunkSize = (int)$this->option('chunk');
        $processed = 0;
        
        DB::table('alunos_iftp')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($alunos) use (&$processed) {
                foreach ($alunos as $aluno) {
                    $this->processAluno($aluno);
                    $this->progressBar->advance();
                    $processed++;
                    
                    // Atualizar informações a cada 100 registros
                    if ($processed % 100 === 0) {
                        $this->updateProgressInfo();
                    }
                }
            });
    }
    
    protected function processAluno(object $aluno): void
    {
        try {
            $alunosSimaed = DB::table('alunos_simaed')
                ->where('nome', $aluno->nome_aluno)
                ->where('situacao_matricula', 'Ativa')
                ->get();
            
            $count = $alunosSimaed->count();
            
            if ($count === 1) {
                $this->updateAlunoComDadosSimaed($aluno->id, $alunosSimaed->first());
            } elseif ($count === 0) {
                $this->updateAlunoObs($aluno->id, 'Não encontrado');
            } else {
                $this->updateAlunoObs($aluno->id, "Encontrados {$count} registros com esse nome");
            }
            
        } catch (\Exception $e) {
            Log::error("Erro ao processar aluno ID {$aluno->id}: " . $e->getMessage());
            $this->updateAlunoObs($aluno->id, 'Erro no processamento');
        }
    }
    
    protected function updateAlunoComDadosSimaed(int $alunoId, object $simaed): void
    {
        DB::table('alunos_iftp')
            ->where('id', $alunoId)
            ->update([
                'cpf' => $simaed->nu_cpf,
                'codigo_escola_estadual' => $simaed->censo,
                'nome_escola_estadual' => $simaed->escola,
                'cidade' => $simaed->municipio,
                'id_inep' => $simaed->cd_inep,
                'obs' => null,
                'updated_at' => now(),
            ]);
    }
    
    protected function updateAlunoObs(int $alunoId, string $observacao): void
    {
        DB::table('alunos_iftp')
            ->where('id', $alunoId)
            ->update([
                'obs' => $observacao,
                'updated_at' => now(),
            ]);
    }
    
    protected function updateProgressInfo(): void
    {
        $elapsed = microtime(true) - $this->startTime;
        $percentComplete = ($this->progressBar->getProgress() / $this->totalAlunos) * 100;
        $estimatedTotal = $elapsed / ($percentComplete / 100);
        $remaining = $estimatedTotal - $elapsed;
        
        $memory = memory_get_usage(true) / 1024 / 1024; // MB
        
        $this->progressBar->setMessage(number_format($elapsed, 2) . 's', 'elapsed');
        $this->progressBar->setMessage(number_format($estimatedTotal, 2) . 's', 'estimated');
        $this->progressBar->setMessage(number_format($memory, 2) . 'MB', 'memory');
    }
    
    protected function finishProgress(): void
    {
        $this->progressBar->finish();
        $this->newLine(2);
        
        $elapsed = microtime(true) - $this->startTime;
        $memoryPeak = memory_get_peak_usage(true) / 1024 / 1024;
        
        $this->info("Tempo total de execução: " . number_format($elapsed, 2) . " segundos");
        $this->info("Pico de uso de memória: " . number_format($memoryPeak, 2) . " MB");
    }
    
    protected function exportResultsToExcel(): void
    {
        $this->info('Exportando resultados para Excel...');
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Cabeçalhos
        $headers = [
            'ID', 'Nome Aluno', 'CPF', 'Código Escola', 
            'Nome Escola', 'Cidade', 'ID INEP', 'Observação'
        ];
        
        $sheet->fromArray($headers, null, 'A1');
        
        // Estilizar cabeçalhos
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        
        // Buscar dados
        $alunos = DB::table('alunos_iftp')
            ->select('id', 'nome_aluno', 'cpf', 'codigo_escola_estadual', 
                    'nome_escola_estadual', 'cidade', 'id_inep', 'obs')
            ->get()
            ->toArray();
        
        // Adicionar dados
        $row = 2;
        foreach ($alunos as $aluno) {
            $sheet->fromArray((array)$aluno, null, "A{$row}");
            $row++;
        }
        
        // Ajustar largura das colunas
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Adicionar bordas
        $lastRow = count($alunos) + 1;
        $sheet->getStyle("A1:H{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
        
        // Salvar arquivo
        $filename = 'resultados_alunos_iftp_' . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path("app/{$filename}"));
        
        $this->info("Arquivo exportado: " . storage_path("app/{$filename}"));
    }
}