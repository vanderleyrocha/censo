<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Censo2025ReportGenerator
{
    public function generate(array $reports, array $successfulSchools = []): string
    {
        // Buscar escolas não encontradas
        $missingSchools = $this->getMissingSchools();
        
        $html = $this->generateHtml($reports, $successfulSchools, $missingSchools);

        $pdf = Pdf::loadHTML($html);
        $filename = 'relatorio-processamento-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
        $filepath = 'censo_2025_correcoes/relatorios/' . $filename;

        Storage::put($filepath, $pdf->output());

        return Storage::path($filepath);
    }

    private function generateHtml(array $reports, array $successfulSchools = [], array $missingSchools = []): string
    {
        $totalErrors = count($reports);
        $totalSchools = count($successfulSchools);
        $totalRecords = array_sum(array_column($successfulSchools, 'registros_importados'));

        // Agrupar escolas por município
        $schoolsByMunicipio = [];
        foreach ($successfulSchools as $school) {
            $municipio = $school['municipio'];
            if (!isset($schoolsByMunicipio[$municipio])) {
                $schoolsByMunicipio[$municipio] = [];
            }
            $schoolsByMunicipio[$municipio][] = $school;
        }

        // Ordenar municípios e escolas dentro de cada município
        ksort($schoolsByMunicipio);
        foreach ($schoolsByMunicipio as &$schools) {
            usort($schools, function ($a, $b) {
                return strcmp($a['nome_escola'], $b['nome_escola']);
            });
        }

        $data = [
            'currentDate' => Carbon::now()->format('d/m/Y H:i:s'),
            'totalErrors' => $totalErrors,
            'totalSchools' => $totalSchools,
            'totalRecords' => $totalRecords,
            'schoolsByMunicipio' => $schoolsByMunicipio,
            'reports' => $reports,
            'missingSchools' => $missingSchools
        ];

        return view('censo.report', $data)->render();
    }

    private function getMissingSchools(): array
    {
        return DB::select("
            SELECT c.nome AS municipio, e.id, e.nome, e.dependencia, e.situacao, e.zona, e.tipo_localizacao
            FROM escolas e 
            JOIN cidades c ON c.id = e.cidade_id
            WHERE e.encontrada IS NULL OR e.encontrada = false
            ORDER BY c.nome, e.nome
        ");
    }
}