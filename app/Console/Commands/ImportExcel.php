<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use Throwable;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class ImportExcel extends Command
{
    protected $signature = 'import:excel 
                            {file : Caminho do arquivo Excel}
                            {table : Nome da tabela destino}
                            {--chunk=1000 : Tamanho do lote para inserção}
                            {--start-row=2 : Linha inicial dos dados (1 para cabeçalho)}
                            {--sheet=0 : Índice da planilha (0-based)}
                            {--truncate : Limpar tabela antes da importação}
                            {--validate : Validar dados antes de inserir}
                            {--log-errors : Logar erros de validação}';

    protected $description = 'Importa dados de arquivo Excel para tabela MySQL com otimização para grandes arquivos';

    protected int $totalRows = 0;
    protected int $processedRows = 0;
    protected int $successfulRows = 0;
    protected array $errors = [];
    protected array $columnMapping = [];

    public function handle(): int
    {
        try {
            $this->importExcelData();
            return self::SUCCESS;
        } catch (ReaderException $e) {
            $this->error("Erro ao ler arquivo Excel: " . $e->getMessage());
            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error("Erro inesperado: " . $e->getMessage());
            Log::error('Erro no comando import:excel', ['exception' => $e]);
            return self::FAILURE;
        }
    }

    protected function importExcelData(): void
    {
        $filePath = $this->argument('file');
        $tableName = $this->argument('table');
        $chunkSize = (int)$this->option('chunk');
        $startRow = (int)$this->option('start-row');
        $sheetIndex = (int)$this->option('sheet');

        $this->validateInputs($filePath, $tableName);

        $this->info("Iniciando importação do arquivo: {$filePath}");
        $this->info("Tabela destino: {$tableName}");
        $this->info("Tamanho do lote: {$chunkSize} registros");

        $spreadsheet = $this->loadSpreadsheet($filePath);
        $worksheet = $spreadsheet->getSheet($sheetIndex);
        
        $this->totalRows = $worksheet->getHighestDataRow();
        $highestColumn = $worksheet->getHighestDataColumn();
        $totalColumns = Coordinate::columnIndexFromString($highestColumn);

        $this->info("Total de linhas: {$this->totalRows}");
        $this->info("Total de colunas: {$totalColumns}");

        // Obter cabeçalhos e mapear colunas
        $headers = $this->getHeaders($worksheet, $startRow - 1);
        $this->columnMapping = $this->mapColumnsToTable($headers, $tableName);

        if ($this->option('truncate')) {
            $this->truncateTable($tableName);
        }

        $progressBar = $this->output->createProgressBar($this->totalRows - $startRow + 1);
        $progressBar->start();

        // Processar dados em lotes usando LazyCollection para otimizar memória
        $this->processDataInChunks(
            $worksheet, 
            $startRow, 
            $chunkSize, 
            $tableName, 
            $progressBar
        );

        $progressBar->finish();
        $this->newLine(2);

        $this->displayResults();
    }

    protected function loadSpreadsheet(string $filePath): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Arquivo não encontrado: {$filePath}");
        }

        $this->info("Carregando arquivo...");

        // Configurar reader para otimização de memória
        $reader = IOFactory::createReaderForFile($filePath);
        
        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }
        
        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }

        return $reader->load($filePath);
    }

    protected function getHeaders(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet, int $headerRow): array
    {
        $headers = [];
        $highestColumn = $worksheet->getHighestDataColumn();
        $totalColumns = Coordinate::columnIndexFromString($highestColumn);

        for ($col = 1; $col <= $totalColumns; $col++) {
            $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $headerRow;
            $cellValue = $worksheet->getCell($cellCoordinate)->getValue();
            $headers[$col] = $this->normalizeHeader($cellValue);
        }

        return $headers;
    }

    protected function normalizeHeader(?string $header): string
    {
        if (empty($header)) {
            return 'unknown_column';
        }

        return Str::snake(Str::lower(preg_replace('/[^a-zA-Z0-9_]/', '_', $header)));
    }

    protected function mapColumnsToTable(array $headers, string $tableName): array
    {
        $tableColumns = Schema::getColumnListing($tableName);
        $mapping = [];

        foreach ($headers as $colIndex => $excelHeader) {
            // Tentar encontrar correspondência exata
            if (in_array($excelHeader, $tableColumns)) {
                $mapping[$colIndex] = $excelHeader;
                continue;
            }

            // Tentar encontrar correspondência parcial
            foreach ($tableColumns as $tableColumn) {
                if (Str::contains($excelHeader, $tableColumn) || 
                    Str::contains($tableColumn, $excelHeader)) {
                    $mapping[$colIndex] = $tableColumn;
                    continue 2;
                }
            }

            // Se não encontrar, usar o header do Excel
            $mapping[$colIndex] = $excelHeader;
        }

        return $mapping;
    }

    protected function processDataInChunks(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet,
        int $startRow,
        int $chunkSize,
        string $tableName,
        $progressBar
    ): void {
        $chunk = [];
        $highestColumn = $worksheet->getHighestDataColumn();
        $totalColumns = Coordinate::columnIndexFromString($highestColumn);

        for ($row = $startRow; $row <= $this->totalRows; $row++) {
            $rowData = $this->processRow($worksheet, $row, $totalColumns);

            if ($rowData !== null) {
                $chunk[] = $rowData;

                if (count($chunk) >= $chunkSize) {
                    $this->insertChunk($chunk, $tableName);
                    $chunk = [];
                }
            }

            $this->processedRows++;
            $progressBar->advance();
        }

        // Inserir dados restantes
        if (!empty($chunk)) {
            $this->insertChunk($chunk, $tableName);
        }
    }

    protected function processRow(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet,
        int $row,
        int $totalColumns
    ): ?array {
        $rowData = [];

        for ($col = 1; $col <= $totalColumns; $col++) {
            $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
            $cell = $worksheet->getCell($cellCoordinate);
            $value = $cell->getValue();

            // Converter datas do Excel
            if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                $value = Carbon::instance(Date::excelToDateTimeObject($value));
            }

            $columnName = $this->columnMapping[$col] ?? "column_{$col}";
            $rowData[$columnName] = $value;
        }

        // Validar dados se necessário
        if ($this->option('validate') && !$this->validateRowData($rowData, $row)) {
            return null;
        }

        // Adicionar timestamps
        if (Schema::hasColumn($this->argument('table'), 'created_at')) {
            $rowData['created_at'] = now();
            $rowData['updated_at'] = now();
        }

        return $rowData;
    }

    protected function validateRowData(array $data, int $rowNumber): bool
    {
        // Criar regras de validação baseadas na estrutura da tabela
        $rules = $this->getValidationRules();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $this->errors[$rowNumber] = $validator->errors()->all();
            
            if ($this->option('log-errors')) {
                Log::warning("Erro de validação na linha {$rowNumber}", [
                    'errors' => $validator->errors()->all(),
                    'data' => $data
                ]);
            }
            
            return false;
        }

        return true;
    }

    protected function getValidationRules(): array
    {
        // Implementar regras de validação baseadas na estrutura da tabela
        // Esta é uma implementação básica - personalize conforme necessário
        $tableName = $this->argument('table');
        $columns = Schema::getColumnListing($tableName);
        $rules = [];

        foreach ($columns as $column) {
            $columnType = Schema::getColumnType($tableName, $column);
            
            switch ($columnType) {
                case 'integer':
                case 'bigint':
                    $rules[$column] = 'nullable|integer';
                    break;
                case 'decimal':
                case 'float':
                    $rules[$column] = 'nullable|numeric';
                    break;
                case 'datetime':
                case 'timestamp':
                    $rules[$column] = 'nullable|date';
                    break;
                case 'string':
                    $rules[$column] = 'nullable|string|max:255';
                    break;
                default:
                    $rules[$column] = 'nullable';
            }
        }

        return $rules;
    }

    protected function insertChunk(array $chunk, string $tableName): void
    {
        try {
            DB::beginTransaction();

            DB::table($tableName)->insert($chunk);
            $this->successfulRows += count($chunk);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("Erro ao inserir lote: " . $e->getMessage());
            Log::error('Erro ao inserir lote no banco de dados', [
                'exception' => $e,
                'table' => $tableName,
                'chunk_size' => count($chunk)
            ]);

            // Tentar inserir linha por linha em caso de erro
            $this->insertRowByRow($chunk, $tableName);
        }
    }

    protected function insertRowByRow(array $chunk, string $tableName): void
    {
        foreach ($chunk as $rowData) {
            try {
                DB::table($tableName)->insert($rowData);
                $this->successfulRows++;
            } catch (\Exception $e) {
                $this->errors[] = "Erro na linha: " . json_encode($rowData) . " - " . $e->getMessage();
            }
        }
    }

    protected function truncateTable(string $tableName): void
    {
        $this->info("Limpando tabela {$tableName}...");
        DB::table($tableName)->truncate();
    }

    protected function validateInputs(string $filePath, string $tableName): void
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Arquivo não encontrado: {$filePath}");
        }

        if (!Schema::hasTable($tableName)) {
            throw new \InvalidArgumentException("Tabela não existe: {$tableName}");
        }

        $allowedExtensions = ['xlsx', 'xls', 'csv', 'ods'];
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (!in_array(strtolower($extension), $allowedExtensions)) {
            throw new \InvalidArgumentException("Extensão de arquivo não suportada: {$extension}");
        }
    }

    protected function displayResults(): void
    {
        $this->info("Importação concluída!");
        $this->info("Total de linhas processadas: {$this->processedRows}");
        $this->info("Linhas importadas com sucesso: {$this->successfulRows}");
        $this->info("Linhas com erro: " . count($this->errors));

        if (!empty($this->errors)) {
            $this->warn("Erros encontrados:");
            foreach (array_slice($this->errors, 0, 10) as $row => $error) {
                $this->line("Linha {$row}: " . (is_array($error) ? implode(', ', $error) : $error));
            }

            if (count($this->errors) > 10) {
                $this->line("... e mais " . (count($this->errors) - 10) . " erros");
            }

            $this->info("Verifique o log para detalhes completos dos erros.");
        }
    }
}