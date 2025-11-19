<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Censo2025ReportGenerator
{
    private array $regionais = [];

    public function setRegionais(array $regionais): void
    {
        $this->regionais = $regionais;
    }

    public function generate(array $reports, array $successfulSchools = []): string
    {
        // MODIFICAÇÃO: Processar dados de forma mais eficiente
        $data = $this->prepareReportData($reports, $successfulSchools);

        // MODIFICAÇÃO: Usar configurações otimizadas para o PDF
        $pdf = Pdf::loadHTML($data['html'])
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'dpi' => 150, // Reduzir DPI para arquivos menores
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false // Desabilitar imagens remotas
            ]);

        $filename = 'relatorio-processamento-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
        $filepath = 'censo_2025_correcoes/relatorios/' . $filename;

        Storage::put($filepath, $pdf->output());

        // MODIFICAÇÃO: Limpar memória
        unset($pdf, $data);

        return Storage::path($filepath);
    }

    private function prepareReportData(array $reports, array $successfulSchools = []): array
    {
        $totalErrors = count($reports);
        $totalSchools = count($successfulSchools);
        $totalRecords = array_sum(array_column($successfulSchools, 'registros_importados'));

        // MODIFICAÇÃO: Agrupar escolas de forma mais eficiente
        $schoolsByMunicipio = [];
        foreach ($successfulSchools as $school) {
            $municipio = $school['municipio'];
            $schoolsByMunicipio[$municipio][] = $school;
        }

        // MODIFICAÇÃO: Ordenar de forma mais eficiente
        ksort($schoolsByMunicipio);

        $missingSchools = $this->getMissingSchools();

        return [
            'html' => view('censo.report', [
                'currentDate' => Carbon::now()->format('d/m/Y H:i:s'),
                'totalErrors' => $totalErrors,
                'totalSchools' => $totalSchools,
                'totalRecords' => $totalRecords,
                'schoolsByMunicipio' => $schoolsByMunicipio,
                'reports' => $reports,
                'missingSchools' => $missingSchools,
                'regionaisFiltradas' => !empty($this->regionais) ? $this->regionais : null
            ])->render(),
            'schoolsByMunicipio' => $schoolsByMunicipio
        ];
    }

    // MODIFICAÇÃO: Otimizar consulta de escolas faltantes com filtro por regionais
    private function getMissingSchools(): array
    {
        $query = "
            SELECT c.nome AS municipio, e.id, e.nome, e.dependencia, e.situacao
            FROM escolas e 
            JOIN cidades c ON c.id = e.cidade_id
            WHERE (e.encontrada IS NULL OR e.encontrada = false)
            AND e.situacao = 'Ativa'
        ";

        // Adicionar filtro por regionais se especificado
        if (!empty($this->regionais)) {
            $placeholders = implode(',', array_fill(0, count($this->regionais), '?'));
            $query .= " AND c.regional_id IN ({$placeholders})";
        }

        $query .= " ORDER BY c.nome, e.nome LIMIT 1000";

        if (!empty($this->regionais)) {
            return DB::select($query, $this->regionais);
        }

        return DB::select($query);
    }
}
