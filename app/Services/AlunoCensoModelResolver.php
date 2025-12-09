<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class AlunoCensoModelResolver
{
    /**
     * Retorna o FQCN da Model de acordo com o ano.
     */
    public function resolve(int $ano): string
    {
        return match ($ano) {
            2023 => \App\Models\Aluno2023::class,
            2024 => \App\Models\Aluno2024::class,
            2025 => \App\Models\Aluno2025::class,
            default => throw new InvalidArgumentException("Ano {$ano} não suportado para AlunoCenso."),
        };
    }
}
