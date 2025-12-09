<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\AlunoCensoFilter;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

final class SpoutAlunoCensoExportStrategy extends AbstractAlunoCensoExportStrategy
{
    public function export(
        Builder $query,
        AlunoCensoFilter $filter,
        ?ExportProgressBar $progressBar = null
    ): string {
        // Colunas definidas via preset no comando
        $columns = $filter->columns;

        // Segurança: se por algum motivo vier vazio, usa preset "geral"
        if (empty($columns)) {
            $columns = AlunoCensoColumnRegistry::resolveColumns('geral');
        }

        $headerKeys = array_keys($columns);
        $headerLabels = array_values($columns);

        $fileName = "exports/alunos_censo_{$filter->ano}_" . now()->format('Ymd_His') . ".xlsx";
        $fullPath = Storage::path($fileName);

        @mkdir(dirname($fullPath), 0775, true);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($fullPath);

        // Estilo do cabeçalho
        $styleHeader = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(11)
            ->build();

        // Linhas superiores (decorativas)
        $writer->addRow(WriterEntityFactory::createRowFromArray([
            'Departamento de Dados e Estatísticas Educacionais',
        ], $styleHeader));

        $writer->addRow(WriterEntityFactory::createRowFromArray([
            'Divisão de Censo Escolar',
        ], $styleHeader));

        $writer->addRow(WriterEntityFactory::createRowFromArray([
            "Relação de Alunos do Censo Escolar - {$filter->ano}",
        ], $styleHeader));

        $writer->addRow(WriterEntityFactory::createRowFromArray([
            $this->buildFiltersLabel($filter),
        ]));

        // Linha em branco
        $writer->addRow(WriterEntityFactory::createRowFromArray([]));

        // Cabeçalho das colunas
        $writer->addRow(
            WriterEntityFactory::createRowFromArray($headerLabels, $styleHeader)
        );

        // Linhas de dados (chunkado)
        $query->chunk(5000, function ($alunos) use ($writer, $headerKeys, $progressBar) {
            foreach ($alunos as $aluno) {
                $row = [];

                foreach ($headerKeys as $col) {
                    $val = $aluno->{$col} ?? null;

                    if ($col === 'cpf' && $val) {
                        $val = $this->formatCpf((string) $val);
                    }

                    if (in_array($col, ['data_nascimento', 'nascimento'], true) && $val) {
                        $val = $this->formatDate((string) $val);
                    }

                    $row[] = $val;
                }

                $writer->addRow(WriterEntityFactory::createRowFromArray($row));
                $progressBar?->advance();
            }
        });

        $writer->close();

        return $fileName;
    }
}
