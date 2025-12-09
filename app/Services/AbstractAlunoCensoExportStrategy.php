<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\AlunoCensoFilter;

abstract class AbstractAlunoCensoExportStrategy implements AlunoCensoExportStrategyInterface
{
    protected function buildFiltersLabel(AlunoCensoFilter $filter): string
    {
        $parts = [];

        foreach ($filter->toArray() as $key => $value) {
            if ($value === null || $value === '' || $value === 'não') {
                continue;
            }

            if (in_array($key, ['ano', 'colunas_preset'], true)) {
                continue;
            }

            $parts[] = "{$key} = {$value}";
        }

        $parts[] = "preset_colunas = {$filter->colunas_preset}";

        return empty($parts)
            ? "Filtros: Nenhum filtro aplicado"
            : "Filtros: " . implode(' | ', $parts);
    }

    protected function formatCpf(string $value): string
    {
        $cpf = preg_replace('/\D/', '', $value ?? '');

        return strlen($cpf) === 11
            ? substr($cpf, 0, 3) . '.' .
            substr($cpf, 3, 3) . '.' .
            substr($cpf, 6, 3) . '-' .
            substr($cpf, 9, 2)
            : $value;
    }

    protected function formatDate(string $value): string
    {
        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return $value;
        }
    }
}
