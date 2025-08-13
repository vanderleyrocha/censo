<?php

namespace App\Console\Commands;

use App\Helpers\Format;
use App\Models\Aluno2025; // Model novo
use \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Helper\ProgressBar;

class ProcessCenso2025Files extends Command
{
    // A assinatura do comando foi alterada para refletir o novo propósito
    protected $signature = 'censo:process-2025
                            {directory : The directory containing Censo 2025 Excel files}
                            {--F|force : Force reprocessing of all files}';

    protected $description = 'Process Excel files from Censo Escolar 2025 (new format)';

    protected ProgressBar $progressBar;

    public function handle()
    {
        $directory = $this->argument('directory');
        // A opção 'force' pode ser usada para reimportar tudo, se necessário.
        // A lógica de pular arquivos já processados foi removida por simplicidade,
        // mas pode ser readicionada se houver uma coluna 'processado' na tabela de escolas.

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

        $this->info("Found {$totalFiles} Excel files to process for Censo 2025.");
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
            'skipped' => 0, // Pode ser usado no futuro
            'failed' => 0,
            'students_added' => 0,
        ];

        foreach ($files as $file) {
            $this->progressBar->setMessage(basename($file));
            $this->processFile($file, $stats);
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
                ['Failed Files',  $stats['failed']],
                ['New Students Added (2025)', $stats['students_added']],
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

    protected function processFile(string $filePath, array &$stats): void
    {
        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Extrai os dados dos alunos usando a nova lógica
            $alunosParaInserir = $this->extractCenso2025Data($worksheet);

            if (empty($alunosParaInserir)) {
                $stats['skipped']++;
                Log::warning("No student data found or extracted from {$filePath}.");
                return;
            }

            DB::transaction(function () use ($alunosParaInserir, &$stats) {
                // Para o novo formato, é mais simples deletar os registros antigos da escola
                // e inserir os novos, para garantir consistência.
                $codInepEscola = $alunosParaInserir[0]['cod_inep_escola'];
                if ($codInepEscola) {
                    Aluno2025::where('cod_inep_escola', $codInepEscola)->delete();
                }

                $chunks = array_chunk($alunosParaInserir, 500); // Insere em lotes de 500
                foreach ($chunks as $chunk) {
                    Aluno2025::insert($chunk);
                }
                $stats['students_added'] += count($alunosParaInserir);
                $stats['processed']++;
            });
        } catch (\Throwable $e) {
            Log::error("Error processing file {$filePath}: {$e->getMessage()}\n" . $e->getTraceAsString());
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

    /**
     * Extrai os dados da planilha no formato Censo 2025.
     * Este método substitui extractSchoolData e extractClassData do script original.
     */
    protected function extractCenso2025Data(Worksheet $worksheet): array
    {
        // 1. Extrair dados da escola (posições fixas) 
        $schoolDataRow = $this->findCellRow($worksheet, 1, 'B', 'Informações da Escola', $worksheet->getHighestRow(), 30);
        Log::info("Linha inicial dos dados sobre a escola: $schoolDataRow" );
        $cod_inep_escola = $worksheet->getCell('K'. ($schoolDataRow + 1))->getValue();
        Log::info("cod_inep_escola: $cod_inep_escola" );
        $schoolData = [
            'cod_inep_escola' => $worksheet->getCell('K'. ($schoolDataRow + 1))->getValue(),
            'nome_escola'     => $worksheet->getCell('K'. ($schoolDataRow + 2))->getValue(),
            'municipio'       => $worksheet->getCell('K'. ($schoolDataRow + 4))->getValue(),
            'localizacao_escola' => $worksheet->getCell('K'. ($schoolDataRow + 5))->getValue(),
            'dependencia_administrativa' => $worksheet->getCell('K'. ($schoolDataRow + 6))->getValue(),
        ];

        if (empty($schoolData['cod_inep_escola'])) {
            // Se não encontrar o código da escola, não pode prosseguir
            return [];
        }

        // 2. Encontrar o cabeçalho para saber onde os dados dos alunos começam 
        $headerRow = $this->findCellRow($worksheet, 1, 'B', 'Ordem', $worksheet->getHighestRow(), 30);
        Log::info("Linha inicial dos dados da turma: $headerRow" );
        if (!$headerRow) {
            return []; // Cabeçalho não encontrado
        }
        $startRow = $headerRow + 1;
        $highestRow = $worksheet->getHighestRow();
        $alunos = [];

        // 3. Mapeamento de colunas para os nomes da tabela (baseado no arquivo .csv)
        $columnMap = [
            'C' => 'cod_inep_aluno',            // Identificação única
            'E' => 'nome',                      // Nome
            'H' => 'nascimento',                // Data de nascimento
            'J' => 'cpf',                       // CPF
            'L' => 'nacionalidade',             // Nacionalidade
            'M' => 'raca',                      // Cor/Raça
            'N' => 'povo_indigena',             // Povo indígena
            'O' => 'sexo',                      // Sexo
            'P' => 'tipo_deficiencia',          // Tipo(s) de deficiência(s)...
            'Q' => 'transtorno_aprendizagem',   // Tipo(s) de transtorno(s)...
            'R' => 'recursos_saeb',             // Recursos para o uso do(a) aluno(a)...
            'S' => 'tipo_atendimento_especializado', // Tipo de atendimento educacional especializado
            'T' => 'espaco_diferente',          // Recebe escolarização em outro espaço...
            'U' => 'localizacao_residencia',    // Localização/Zona de residência
            'V' => 'localizacao_diferente',     // Localização diferenciada de residência
            'W' => 'transporte_escolar',        // Transporte escolar (Sim/Não)
            'X' => 'transporte_publico',        // Poder Público responsável
            'Y' => 'tipo_veiculo',              // Tipo de veículo utilizado...
            'Z' => 'etapa_vinculo',             // Etapa de vínculo do(a) aluno(a)...
            'AA' => 'codigo_matricula',         // Código da Matrícula
            'AB' => 'codigo_turma',             // Código da turma
            'AC' => 'nome_turma',               // Nome da turma
            'AD' => 'tipo_mediacao',            // Tipo de mediação didático-pedagógica
            'AF' => 'etapa_agregada',           // Etapa Agregada
            'AG' => 'etapa_ensino',             // Etapa de ensino
            'AH' => 'forma_organizacao',        // Formas de organização da turma
            'AI' => 'local_diferenciado',       // Local de funcionamento diferenciado...
            'AJ' => 'horario_funcionamento',    // Dias da semana e horário...
            'AK' => 'carga_horaria_semanal',    // Carga horária semanal (hh:mm)
            'AL' => 'turma_aee',                // Turma de educação especial...
            'AM' => 'turma_bilingue',           // Turma de Educação Bilingue de Surdos...
            'AN' => 'turma_alternancia',        // Turma de Formação por Alternância...
            'AO' => 'area_conhecimento',        // Áreas do conhecimento/componentes...
            'AP' => 'organizacao_curricular',   // Organização curricular da turma
            'AQ' => 'itinerario_formativo',     // Área(s) do itinerário formativo
            'AR' => 'tipo_ftp',                 // Tipo do curso do itinerário de formação...
            'AS' => 'codigo_nome_ftp',          // Código e nome do curso técnico
            'AT' => 'atividade_complementar',   // Atividade(s) complementar(es)
        ];

        // 4. Iterar sobre as linhas de dados
        for ($row = $startRow; $row <= $highestRow; $row++) {
            $codAluno = $worksheet->getCell("B{$row}")->getValue();
            // Para na primeira linha que não tiver um código de aluno válido
            if (empty($codAluno) || !is_numeric($codAluno)) {
                break;
            }

            $alunoData = [];
            foreach ($columnMap as $col => $dbField) {
                $alunoData[$dbField] = $worksheet->getCell("{$col}{$row}")->getValue();
            }

            // 5. Formatar e limpar os dados
            $alunoData['nascimento'] = Format::dateBRtoEn($alunoData['nascimento']);
            $alunoData['cpf'] = Format::digitOnly($alunoData['cpf']);
            $alunoData['povo_indigena'] = Format::digitOnly($alunoData['povo_indigena']);

            // Exemplo de conversão de Sim/Não para booleano (ou 1/0)
            $alunoData['transporte_escolar'] = (strtolower($alunoData['transporte_escolar']) == 'sim' ? 1 : 0);
            $alunoData['turma_aee'] = (strtolower($alunoData['turma_aee']) == 'sim' ? 1 : 0);
            $alunoData['turma_bilingue'] = (strtolower($alunoData['turma_bilingue']) == 'sim' ? 1 : 0);
            $alunoData['turma_alternancia'] = (strtolower($alunoData['turma_alternancia']) == 'sim' ? 1 : 0);

            // Adicionar os dados da escola e combinar com os dados do aluno
            $alunos[] = array_merge($schoolData, $alunoData);
        }
        Log::info("Última linha lida: $row" );
        return $alunos;
    }

    // Função auxiliar para encontrar uma linha, igual à do script original
    protected function findCellRow(Worksheet $worksheet, int $startRow, string $column, string $searchText, int $maxRow, int $maxSearch = 100): ?int
    {
        $currentRow = $startRow;
        $endRow = min($startRow + $maxSearch, $maxRow);
        Log::info("Texto procurado: $searchText - linha inicial: $startRow - linha final: $endRow" );
        while ($currentRow <= $endRow) {
            $cellValue = $worksheet->getCell("{$column}{$currentRow}")->getValue();
            Log::info("Compara: $searchText - com: $cellValue - endereço: {$column}{$currentRow}" );
            if (trim($cellValue) == $searchText) {
                Log::info("Texto encontrado na linha: $currentRow" );
                return $currentRow;
            }
            $currentRow++;
        }
        Log::info("Texto $searchText não encontrado" );
        return null;
    }
}
