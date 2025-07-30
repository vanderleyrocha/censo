<?php

namespace App\Http\Controllers;

use App\Models\Regiao;
use App\Models\Servidor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RegiaoController extends Controller
{
    public function index(Request $request)
    {
        $query = Regiao::with(['servidor'])
            ->orderBy('nome');

        // Filtros
        if ($request->filled('servidor_id')) {
            $query->where('servidor_id', $request->servidor_id);
        }

        $regioes = $query->paginate(10)->withQueryString();

        return Inertia::render('Regioes/Index', [
            'regioes' => $regioes,
            'servidores' => Servidor::orderBy('nome')->get(),
            'filters' => $request->only(['servidor_id'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Regioes/Create', [
            'servidores' => Servidor::orderBy('nome')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'sigla' => 'required|string|max:10',
            'servidor_id' => 'nullable|exists:servidores,id'
        ]);

        Regiao::create($request->all());

        return redirect()->route('regioes.index')
            ->with('success', 'Região criada com sucesso!');
    }

    public function edit(Regiao $regiao)
    {
        return Inertia::render('Regioes/Edit', [
            'regiao' => $regiao,
            'servidores' => Servidor::orderBy('nome')->get()
        ]);
    }

    public function update(Request $request, Regiao $regiao)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'sigla' => 'required|string|max:10',
            'servidor_id' => 'nullable|exists:servidores,id'
        ]);

        $regiao->update($request->all());

        return redirect()->route('regioes.index')
            ->with('success', 'Região atualizada com sucesso!');
    }

    public function destroy(Regiao $regiao)
    {
        $regiao->delete();

        return redirect()->route('regioes.index')
            ->with('success', 'Região removida com sucesso!');
    }
}