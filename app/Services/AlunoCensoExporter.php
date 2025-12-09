<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\AlunoCensoFilter;
use Illuminate\Database\Eloquent\Builder;

final class AlunoCensoExporter
{
    public function __construct(
        private readonly SpoutAlunoCensoExportStrategy $spoutStrategy,
        private readonly PhpSpreadsheetAlunoCensoExportStrategy $phpSpreadsheetStrategy,
    ) {}


    public function exportWithSpout(
        Builder $query,
        AlunoCensoFilter $filter,
        ?ExportProgressBar $progressBar = null
    ): string {
        return $this->spoutStrategy->export($query, $filter, $progressBar);
    }

    public function exportWithPhpSpreadsheet(
        Builder $query,
        AlunoCensoFilter $filter,
        ?ExportProgressBar $progressBar = null
    ): string {
        return $this->phpSpreadsheetStrategy->export($query, $filter, $progressBar);
    }


    public function export(
        Builder $query,
        AlunoCensoFilter $filter,
        string $driver = 'phpspreadsheet',
        ?ExportProgressBar $progressBar = null
    ): string {
        return match (strtolower($driver)) {
            'spout'          => $this->exportWithSpout($query, $filter, $progressBar),
            'phpspreadsheet' => $this->exportWithPhpSpreadsheet($query, $filter, $progressBar),
            default          => throw new \InvalidArgumentException("Driver de exportação inválido: {$driver}"),
        };
    }
}
