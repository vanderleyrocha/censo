<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\AlunoCensoFilter;
use Illuminate\Database\Eloquent\Builder;

final class AlunoCensoRepository
{
    /**
     * @param  class-string  $modelClass
     * @return Builder
     */
    public function buscar(string $modelClass, AlunoCensoFilter $filter): Builder
    {
        /** @var Builder $query */
        $query = $modelClass::query();

        // Filtros opcionais
        if ($filter->inclusos) {
            $query->where(function (Builder $query) {
                $query
                    ->whereNotNull('tipo_deficiencia')
                    ->orWhereNotNull('tipo_transtorno')
                    ->orWhereNotNull('tipo_aee');
            });
        }

        if ($filter->dependencia_administrativa !== null) {
            $query->where('dependencia_administrativa', $filter->dependencia_administrativa);
        }

        if ($filter->municipio !== null) {
            $query->where('municipio', $filter->municipio);
        }

        if ($filter->localizacao_escola !== null) {
            $query->where('localizacao_escola', $filter->localizacao_escola);
        }

        if ($filter->cod_inep_escola !== null) {
            $query->where('cod_inep_escola', $filter->cod_inep_escola);
        }

        if ($filter->tipo_turma !== null) {
            $query->where('tipo_turma', $filter->tipo_turma);
        }

        if ($filter->tipo_mediacao !== null) {
            $query->where('tipo_mediacao', $filter->tipo_mediacao);
        }

        if ($filter->formas_organizacao_turma !== null) {
            $query->where('formas_organizacao_turma', $filter->formas_organizacao_turma);
        }

        if ($filter->turma_formacao_alternancia !== null) {
            $query->where('turma_formacao_alternancia', $filter->turma_formacao_alternancia);
        }

        // Ordenações padrão
        return $query
            ->orderBy('municipio')
            ->orderBy('nome_escola')
            ->orderBy('nome');
    }
}
