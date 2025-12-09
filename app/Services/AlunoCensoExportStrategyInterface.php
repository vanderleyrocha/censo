<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\AlunoCensoFilter;
use Illuminate\Database\Eloquent\Builder;

interface AlunoCensoExportStrategyInterface
{
    public function export(
        Builder $query,
        AlunoCensoFilter $filter,
        ?ExportProgressBar $progressBar = null
    ): string;
}
