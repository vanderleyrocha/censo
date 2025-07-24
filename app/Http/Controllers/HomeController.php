<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Escola;

class HomeController extends Controller
{
    public function index()
    {
        // Contar escolas em funcionamento
        $escolasEmFuncionamento = Escola::where('situacao', 'Em funcionamento')->count();

        // Obter distribuição por município
        $distribuicaoPorMunicipio = Escola::selectRaw('cidades.nome as municipio, count(*) as total')
            ->join('cidades', 'escolas.cidade_id', '=', 'cidades.id')
            ->where('escolas.situacao', 'Em funcionamento')
            ->groupBy('cidades.nome')
            ->orderBy('total', 'desc')
            ->get();

        return Inertia::render('HomeAutenticada', [
            'headerTitle' => 'Painel de Controle',
            'escolasEmFuncionamento' => $escolasEmFuncionamento,
            'distribuicaoPorMunicipio' => $distribuicaoPorMunicipio
        ]);
    }

    public function escolas_por_tecnico()
    {

        // Contar escolas em funcionamento
        $escolasEmFuncionamento = Escola::where('situacao', 'Em funcionamento')->count();

        // Obter distribuição por município
        $distribuicaoPorTecnico = Escola::selectRaw('servidores.nome as servidor, count(*) as total')
            ->join('servidores', 'escolas.responsavel_censo', '=', 'servidores.id')
            ->where('escolas.situacao', 'Em funcionamento')
            ->groupBy('servidores.nome')
            ->orderBy('servidores.nome')
            ->get()
        ;

        // return $distribuicaoPorTecnico;

        return Inertia::render('GraficoTecnicos', [
            'headerTitle' => 'Escolas por Técnico',
            'escolasEmFuncionamento' => $escolasEmFuncionamento,
            'distribuicaoPorTecnico' => $distribuicaoPorTecnico
        ]);
    }
}
