<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateAlunos extends Command
{
    protected $signature = 'alunos:clean-duplicates 
                            {--batch-size=50000 : Tamanho do lote para deletar}
                            {--disable-fk : Desabilitar checagem de chaves estrangeiras}';
    
    protected $description = 'Remove registros duplicados de alunos mantendo apenas o com maior ID';

    public function handle()
    {
        $this->info('Iniciando limpeza de alunos duplicados (abordagem otimizada)...');
        
        $startTime = microtime(true);
        
        // 1. Desabilitar constraints temporariamente (se necessário)
        if ($this->option('disable-fk')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $this->info('Checagem de FK desabilitada temporariamente');
        }
        
        // 2. Criar tabela temporária com os IDs a manter
        $this->info('Criando tabela temporária com IDs únicos...');
        DB::statement('DROP TEMPORARY TABLE IF EXISTS alunos_ids_manter');
        DB::statement('CREATE TEMPORARY TABLE alunos_ids_manter AS
            SELECT MAX(id) as id
            FROM alunos
            GROUP BY cod_inep_aluno, cpf, nome, cod_inep_escola, 
                   modalidade, etapa, nome_turma, tipo_mediacao, tipo_atendimento');
        
        // 3. Criar índice na tabela temporária para performance
        DB::statement('ALTER TABLE alunos_ids_manter ADD PRIMARY KEY (id)');
        
        // 4. Deletar em grandes lotes os registros que não estão na tabela temporária
        $this->info('Iniciando processo de deleção em lotes...');
        $batchSize = $this->option('batch-size');
        $totalDeleted = 0;
        
        do {
            $deleted = DB::delete("
                DELETE FROM alunos
                WHERE id NOT IN (SELECT id FROM alunos_ids_manter)
                LIMIT {$batchSize}
            ");
            
            $totalDeleted += $deleted;
            $this->info("Registros deletados neste lote: {$deleted} | Total: {$totalDeleted}");
            
            // Pequena pausa para evitar sobrecarga
            if ($deleted > 0) {
                sleep(1);
            }
            
        } while ($deleted > 0);
        
        // 5. Reabilitar constraints (se foram desabilitadas)
        if ($this->option('disable-fk')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('Checagem de FK reabilitada');
        }
        
        // 6. Limpar tabela temporária
        DB::statement('DROP TEMPORARY TABLE IF EXISTS alunos_ids_manter');
        
        $totalTime = round((microtime(true) - $startTime) / 60, 2);
        $this->info("Processo concluído em {$totalTime} minutos!");
        $this->info("Total de registros duplicados removidos: {$totalDeleted}");
        
        return Command::SUCCESS;
    }
}