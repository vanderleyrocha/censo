<?php

namespace App\Console\Commands;

use App\Models\Escola;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;

class UpdateAlunosCount extends Command
{
    protected $signature = 'escolas:update-alunos-count';

    protected $description = 'Atualiza os campos alunos_censo_2024 e alunos_simaed na tabela escolas';

    public function handle()
    {
        $this->info('Iniciando atualização dos contadores de alunos...');
        $this->newLine();

        $totalEscolas = Escola::count();
        
        if ($totalEscolas === 0) {
            $this->error('Nenhuma escola encontrada no banco de dados.');
            return Command::FAILURE;
        }

        $progressBar = $this->output->createProgressBar($totalEscolas);
        $progressBar->setFormat(
            "%current%/%max% [%bar%] %percent:3s%%\n" .
            "Elapsed: %elapsed:6s%\n" .
            "Remaining: %remaining:6s%\n" .
            "Memory: %memory:6s%\n" .
            "Processing: %message%"
        );
        $progressBar->setMessage('Preparando...');
        $progressBar->start();

        // Desabilitar logs de queries para melhor performance
        DB::disableQueryLog();

        // Processar em blocos maiores
        Escola::query()
            ->chunkById(500, function ($escolas) use ($progressBar) {
                // Pré-carregar IDs das escolas para consultas otimizadas
                $escolaIds = $escolas->pluck('id')->toArray();

                // Obter contagens de alunos do Censo 2024 em uma única consulta
                $alunosCensoCounts = DB::table('alunos')
                    ->select('cod_inep_escola', DB::raw('COUNT(*) as total'))
                    ->whereIn('cod_inep_escola', $escolaIds)
                    ->where('ano_censo', 2024)
                    ->where('registro_unico', 1)
                    ->groupBy('cod_inep_escola')
                    ->pluck('total', 'cod_inep_escola');

                // Obter contagens de alunos do SIMAED em uma única consulta
                $alunosSimaedCounts = DB::table('alunos_simaed')
                    ->select('censo', DB::raw('COUNT(*) as total'))
                    ->whereIn('censo', $escolaIds)
                    ->where('situacao_matricula', 'Ativa')
                    ->groupBy('censo')
                    ->pluck('total', 'censo');

                // Atualizar todas as escolas do bloco de uma vez
                foreach ($escolas as $escola) {
                    $progressBar->setMessage("Processando escola ID: {$escola->id}");

                    $escola->updateQuietly([
                        'alunos_censo_2024' => $alunosCensoCounts[$escola->id] ?? 0,
                        'alunos_simaed' => $alunosSimaedCounts[$escola->id] ?? 0,
                    ]);

                    $progressBar->advance();
                }
            });

        $progressBar->finish();
        $this->newLine(2);

        $this->info('Atualização dos contadores de alunos concluída com sucesso!');
        return Command::SUCCESS;
    }
}