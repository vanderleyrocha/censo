<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DTO\AlunoCensoFilter;
use App\Repositories\AlunoCensoRepository;
use App\Services\AlunoCensoColumnRegistry;
use App\Services\AlunoCensoExporter;
use App\Services\AlunoCensoModelResolver;
use App\Services\ExportProgressBar;
use Illuminate\Console\Command;
use InvalidArgumentException;

final class ListarAlunoCenso extends Command
{
    protected $signature = 'censo:listar-alunos
        {ano : Ano do Censo (2023, 2024, 2025)}
        {--colunas=geral : Preset de colunas (geral, parcial, inclusao, transporte, ...)}
        {--inclusos : Lista apenas alunos com algum tipo de deficiência ou transtorno}
        {--dependencia_administrativa=}
        {--municipio=}
        {--localizacao_escola=}
        {--cod_inep_escola=}
        {--tipo_turma=}
        {--tipo_mediacao=}
        {--formas_organizacao_turma=}
        {--turma_formacao_alternancia=}
    ';

    protected $description = 'Gera planilha Excel com a relação de alunos do Censo Escolar';

    public function __construct(
        private readonly AlunoCensoModelResolver $modelResolver,
        private readonly AlunoCensoRepository $repository,
        private readonly AlunoCensoExporter $exporter
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            // ---------------------------------------------------
            // 1) Validação dos argumentos
            // ---------------------------------------------------
            $ano = (int) $this->argument('ano');

            if ($ano < 2000 || $ano > 2100) {
                throw new InvalidArgumentException('O parâmetro "ano" é inválido.');
            }

            $colunasPreset = strtolower((string) ($this->option('colunas') ?? 'geral'));

            // Resolve preset de colunas
            $columns = AlunoCensoColumnRegistry::resolveColumns($colunasPreset);

            // ---------------------------------------------------
            // 2) Construção do filtro
            // ---------------------------------------------------
            $filter = new AlunoCensoFilter(
                ano: $ano,
                inclusos: (bool) $this->option('inclusos'),
                dependencia_administrativa: $this->normalizeOption('dependencia_administrativa'),
                municipio: $this->normalizeOption('municipio'),
                localizacao_escola: $this->normalizeOption('localizacao_escola'),
                cod_inep_escola: $this->normalizeOption('cod_inep_escola'),
                tipo_turma: $this->normalizeOption('tipo_turma'),
                tipo_mediacao: $this->normalizeOption('tipo_mediacao'),
                formas_organizacao_turma: $this->normalizeOption('formas_organizacao_turma'),
                turma_formacao_alternancia: $this->normalizeOption('turma_formacao_alternancia'),
                colunas_preset: $colunasPreset,
                columns: $columns
            );

            // ---------------------------------------------------
            // 3) Resolve qual Model corresponde ao ano
            // ---------------------------------------------------
            $modelClass = $this->modelResolver->resolve($filter->ano);
            $this->info("Buscando alunos na tabela: {$modelClass}");

            // ---------------------------------------------------
            // 4) Obtém a QUERY ao invés de carregar Collection
            // ---------------------------------------------------
            $query = $this->repository->buscar($modelClass, $filter);
            $count = $query->count();

            if ($count === 0) {
                $this->warn('Nenhum aluno encontrado com os filtros informados.');
                return self::SUCCESS;
            }

            $this->info("Foram encontrados {$count} registros.");

            // ---------------------------------------------------
            // 5) Seleção automática da estratégia
            // ---------------------------------------------------
            // - Grandes volumes → Spout
            // - Arquivos padrão → PhpSpreadsheet (mesclagens, filtros, etc.)
            $driver = $count > 100000 ? 'spout' : 'phpspreadsheet';

            $this->info("Gerando planilha usando driver: {$driver}...");
            $this->info("Preset de colunas selecionado: {$colunasPreset}");

            // ---------------------------------------------------
            // 6) Inicializa barra de progresso
            // ---------------------------------------------------
            $progressBar = new ExportProgressBar($this->output);
            $progressBar->start($count);

            $relativePath = $this->exporter->export(
                query: $query,
                filter: $filter,
                driver: $driver,
                progressBar: $progressBar
            );

            // ---------------------------------------------------
            // 7) Finalização
            // ---------------------------------------------------
            $progressBar->finish();

            $this->info("\nPlanilha gerada com sucesso em: storage/app/{$relativePath}");

            return self::SUCCESS;
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return self::INVALID;
        } catch (\Throwable $e) {
            $this->error('Erro ao gerar planilha: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function normalizeOption(string $name): ?string
    {
        $value = $this->option($name);

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
