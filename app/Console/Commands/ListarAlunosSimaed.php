<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Escola;
use App\Models\AlunoSimaed;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ListarAlunosSimaed extends Command
{
    protected $signature = 'simaed:listar';
    protected $description = 'Gera uma planilha com dados de alunos SIMAED das escolas de tempo integral';

    public function handle()
    {
        $this->info("Buscando escolas ...");

        // 1. Seleção das escolas (JOIN com cidades)
        $escolas = Escola::query()
            ->select(
                'cidades.nome AS municipio',
                'escolas.id AS cod_inep',
                'escolas.nome AS escola'
            )
            ->join('cidades', 'cidades.id', '=', 'escolas.cidade_id')
            ->where('escolas.tempo_integral', 1)
            ->orderBy('cidades.nome')
            ->orderBy('escolas.nome')
            ->get();

        $this->info("Processando contagens ...");

        // 2. Acrescentar contagens
        $dados = $escolas->map(function ($escola) {

            $id = $escola->cod_inep;

            return [
                'municipio'      => $escola->municipio,
                'cod_inep'       => $id,
                'escola'         => $escola->escola,

                // Etapas do EM integral
                'etapa1'         => $this->countAlunos($id, 25, 'INTEGRAL'),
                'etapa2'         => $this->countAlunos($id, 26, 'INTEGRAL'),
                'etapa3'         => $this->countAlunos($id, 27, 'INTEGRAL'),

                // EM não integral
                'em_nao_integral'   => AlunoSimaed::where('censo', $id)
                    ->whereIn('etapa_sgp', [25, 26, 27])
                    ->where('situacao_matricula', 'Ativa')
                    ->where('turno', '!=', 'INTEGRAL')
                    ->count(),

                // Outras etapas
                'outras_etapas_integral'  => AlunoSimaed::where('censo', $id)
                    ->whereNotIn('etapa_sgp', [25, 26, 27, 99])
                    ->where('turno', 'INTEGRAL')
                    ->where('situacao_matricula', 'Ativa')
                    ->count(),
                'outras_etapas_nao_integral'  => AlunoSimaed::where('censo', $id)
                    ->whereNotIn('etapa_sgp', [25, 26, 27, 99])
                    ->where('turno', '!=', 'INTEGRAL')
                    ->where('situacao_matricula', 'Ativa')
                    ->count(),
            ];
        });

        $this->info("Gerando planilha ...");
        $this->generateSpreadsheet($dados);

        $this->info("Arquivo gerado em storage/app/simaed_alunos.xlsx");
        return Command::SUCCESS;
    }

    private function countAlunos(int $escolaId, int $etapa, string $turno)
    {
        return AlunoSimaed::where('censo', $escolaId)
            ->where('etapa_sgp', $etapa)
            ->where('situacao_matricula', 'Ativa')
            ->where('turno', $turno)
            ->count();
    }

    private function generateSpreadsheet($dados)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 4. Mapeamento das colunas
        $columns = [
            'municipio'      => 'Município',
            'cod_inep'       => 'Código INEP',
            'escola'         => 'Escola',
            'etapa1'         => '1ª Série',
            'etapa2'         => '2ª Série',
            'etapa3'         => '3ª Série',
            'em_nao_integral'   => 'E M não integral',
            'outras_etapas_integral'  => 'Outras Etapas (Integral)',
            'outras_etapas_nao_integral'  => 'Outras Etapas (não Integral)',
        ];

        // 5. Três linhas de título com mesclagem
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');

        $sheet->setCellValue('A1', 'Departamento de Dados e Estatísticas Educacionais');
        $sheet->setCellValue('A2', 'Divisão de Sistema Educacional de Monitoramento Escolar');
        $sheet->setCellValue('A3', 'Total de alunos do Ensino Médio Integral por etapa de ensino');

        foreach (['A1', 'A2', 'A3'] as $c) {
            $sheet->getStyle($c)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Cabeçalho a partir da linha 5
        $headerRow = 5;
        $colIndex = 1;

        foreach ($columns as $key => $title) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("$col{$headerRow}", $title);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $colIndex++;
        }

        // Aplicar estilo ao cabeçalho
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9D9D9']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ],
        ]);

        // Inserir dados
        $row = $headerRow + 1;

        foreach ($dados as $item) {
            $colIndex = 1;
            foreach ($columns as $key => $label) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue("$col{$row}", $item[$key]);
                $colIndex++;
            }
            $row++;
        }

        // Aplicar filtros
        $sheet->setAutoFilter("A{$headerRow}:I{$headerRow}");

        // Bordas na área dos dados
        $sheet->getStyle("A{$headerRow}:I" . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ]);

        // Salvar arquivo
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('app/simaed_alunos_integral_em.xlsx'));
    }
}
