<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Str;

class Censo2025FileHandler
{
    private const SOURCE_PATH = 'censo_2025_correcoes/não processadas';
    private const PROCESSED_PATH = 'censo_2025_correcoes/processadas';

    public function getFilesToProcess(): array
    {
        $path = Storage::path(self::SOURCE_PATH);
        Log::info("executando método Censo2025FileHandler@getFilesToProcess - path = {$path}");
        
        // Usar allFiles() para buscar arquivos recursivamente em todas as subpastas
        $files = Storage::allFiles(self::SOURCE_PATH);

        return array_filter($files, function ($file) {
            return in_array(pathinfo($file, PATHINFO_EXTENSION), ['xlsx', 'xls']);
        });
    }

    public function loadSpreadsheet(string $filePath): Spreadsheet
    {
        $fullPath = Storage::path($filePath);
        return IOFactory::load($fullPath);
    }

    public function moveProcessedFile(string $sourcePath, string $municipio, int $schoolId, string $schoolName): string
    {
        $fileName = pathinfo($sourcePath, PATHINFO_BASENAME);
        $slug = Str::slug($schoolName);
        $counter = $this->getFileCounter($municipio, $schoolId, $slug);

        $newFileName = "{$slug}-{$schoolId}-{$counter}.xlsx";
        $destinationPath = self::PROCESSED_PATH . '/' . $municipio . '/' . $newFileName;

        $directoryPath = dirname($destinationPath);
        if (!Storage::exists($directoryPath)) {
            Storage::makeDirectory($directoryPath);
        }

        Storage::move($sourcePath, $destinationPath);

        return $destinationPath;
    }

    public function copyProcessedFile(string $sourcePath, string $municipio, int $schoolId, string $schoolName): string
    {
        $fileName = pathinfo($sourcePath, PATHINFO_BASENAME);
        $slug = Str::slug($schoolName);
        $counter = $this->getFileCounter($municipio, $schoolId, $slug);

        $newFileName = "{$slug}-{$schoolId}-{$counter}.xlsx";
        $destinationPath = self::PROCESSED_PATH . '/' . $municipio . '/' . $newFileName;

        $directoryPath = dirname($destinationPath);
        if (!Storage::exists($directoryPath)) {
            Storage::makeDirectory($directoryPath);
        }

        Storage::copy($sourcePath, $destinationPath);

        return $destinationPath;
    }

    private function getFileCounter(string $municipio, int $schoolId, string $slug): int
    {
        // CORREÇÃO: Usar caminho relativo do Storage
        $directoryPath = self::PROCESSED_PATH . '/' . $municipio;
        $pattern = "{$slug}-{$schoolId}-*.xlsx";

        $existingFiles = Storage::files($directoryPath);

        $matchingFiles = array_filter($existingFiles, function ($file) use ($slug, $schoolId) {
            return preg_match("/{$slug}-{$schoolId}-(\d+)\.xlsx$/", basename($file));
        });

        return count($matchingFiles) + 1;
    }
}