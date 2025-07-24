<?php

namespace App\Http\Controllers;

use App\Models\Escola;
use App\Models\Cidade;
use App\Models\Servidor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EscolaController extends Controller
{
    public function index(Request $request)
    {
        $query = Escola::query()
            ->with('cidade')
            ->select([
                'id',
                'nome',
                'dependencia',
                'zona',
                'alunos_simaed',
                'alunos_censo_2024',
                'cidade_id'
            ]);

        // Aplicar filtros
        if ($request->filled('cidade_id')) {
            $query->where('cidade_id', $request->cidade_id);
        }

        if ($request->filled('dependencia')) {
            $query->where('dependencia', $request->dependencia);
        }

        if ($request->filled('zona')) {
            $query->where('zona', $request->zona);
        }

        $escolas = $query->paginate(15)->withQueryString();

        return Inertia::render('Escolas/Index', [
            'headerTitle' => 'Escolas',
            'escolas' => $escolas,
            'cidades' => Cidade::where("estado_id", 1)->orderBy('nome')->get(['id', 'nome']),
            'filters' => $request->only(['cidade_id', 'dependencia', 'zona']),
            'dependencias' => Escola::select('dependencia')->distinct()->pluck('dependencia'),
            'zonas' => Escola::select('zona')->distinct()->pluck('zona'),
        ]);
    }

    public function atribuir(Request $request)
    {
        $cidades = Cidade::where("estado_id", 1)->orderBy('nome')->get();
        $dependencias = Escola::select('dependencia')->distinct()->pluck('dependencia');
        $zonas = Escola::select('zona')->distinct()->pluck('zona');
        $tecnicos = Servidor::whereIn('funcao', ['TÉCNICO', 'ASSESSOR TÉCNICO'])->orderBy('nome')->get(['id', 'nome']);

        $escolas = Escola::query()
            ->with(['cidade', 'responsavel'])
            ->when($request->cidade_id, fn($query, $cidade_id) => $query->where('cidade_id', $cidade_id))
            ->when($request->dependencia, fn($query, $dependencia) => $query->where('dependencia', $dependencia))
            ->when($request->zona, fn($query, $zona) => $query->where('zona', $zona))
            ->orderBy('nome')
            ->get();

        return Inertia::render('Escolas/Atribuir', [
            'headerTitle' => 'Atribuir Técnico Responsável pela Escola',
            'escolas' => $escolas,
            'filters' => $request->only(['cidade_id', 'dependencia', 'zona']),
            'cidades' => $cidades,
            'dependencias' => $dependencias,
            'zonas' => $zonas,
            'tecnicos' => $tecnicos,
        ]);
    }

    public function atualizarResponsavel(Request $request)
    {
        $request->validate([
            'escolas_ids' => 'required|array',
            'escolas_ids.*' => 'exists:escolas,id',
            'servidor_id' => 'required|exists:servidores,id',
        ]);

        // return $request;

        Escola::whereIn('id', $request->escolas_ids)
            ->update(['responsavel_censo' => $request->servidor_id]);

        return redirect()->back()->with('success', 'Responsável atualizado com sucesso!');
    }
}
