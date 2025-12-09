<?php

declare(strict_types=1);

namespace App\DTO;

final class AlunoCensoFilter
{
    public function __construct(
        public int $ano,
        public bool $inclusos = false,
        public ?string $dependencia_administrativa = null,
        public ?string $municipio = null,
        public ?string $localizacao_escola = null,
        public ?string $cod_inep_escola = null,
        public ?string $tipo_turma = null,
        public ?string $tipo_mediacao = null,
        public ?string $formas_organizacao_turma = null,
        public ?string $turma_formacao_alternancia = null,
        // Nome do preset de colunas usado (geral, parcial, inclusao, transporte, etc.)
        public string $colunas_preset = 'geral',
        /** @var array<string,string> Mapa coluna => rótulo */
        public array $columns = [],
    ) {}

    /**
     * Usado para montar descrição dos filtros no cabeçalho da planilha.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'ano' => $this->ano,
            'inclusos' => $this->inclusos ? 'sim' : 'não',
            'dependencia_administrativa' => $this->dependencia_administrativa,
            'municipio' => $this->municipio,
            'localizacao_escola' => $this->localizacao_escola,
            'cod_inep_escola' => $this->cod_inep_escola,
            'tipo_turma' => $this->tipo_turma,
            'tipo_mediacao' => $this->tipo_mediacao,
            'formas_organizacao_turma' => $this->formas_organizacao_turma,
            'turma_formacao_alternancia' => $this->turma_formacao_alternancia,
            'colunas_preset' => $this->colunas_preset,
        ];
    }
}
