<?php

namespace App\Http\Controllers;

use App\Models\Regional;
use App\Models\Regiao;
use App\Models\Servidor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegionalController extends Controller
{
    public function index(Request $request)
    {
        $query = Regional::with(['servidor', 'regiao'])
            ->orderBy('nome');

        // Filtros
        if ($request->filled('servidor_id')) {
            $query->where('servidor_id', $request->servidor_id);
        }

        if ($request->filled('regiao_id')) {
            $query->where('regiao_id', $request->regiao_id);
        }

        $regionais = $query->paginate(10)->withQueryString();

        return Inertia::render('Regionais/Index', [
            'regionais' => $regionais,
            'servidores' => Servidor::orderBy('nome')->get(),
            'regioes' => Regiao::orderBy('nome')->get(),
            'filters' => $request->only(['servidor_id', 'regiao_id'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Regionais/Create', [
            'servidores' => Servidor::orderBy('nome')->get(),
            'regioes' => Regiao::orderBy('nome')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'sigla' => 'required|string|max:10',
            'servidor_id' => 'nullable|exists:servidores,id',
            'regiao_id' => 'nullable|exists:regioes,id'
        ]);

        Regional::create($request->all());

        return redirect()->route('regionais.index')
            ->with('success', 'Regional criada com sucesso!');
    }

    public function edit(Regional $regional)
    {
        return Inertia::render('Regionais/Edit', [
            'regional' => $regional,
            'servidores' => Servidor::orderBy('nome')->get(),
            'regioes' => Regiao::orderBy('nome')->get()
        ]);
    }

    public function update(Request $request, Regional $regional)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'sigla' => 'required|string|max:10',
            'servidor_id' => 'nullable|exists:servidores,id',
            'regiao_id' => 'nullable|exists:regioes,id'
        ]);

        $regional->update($request->all());

        return redirect()->route('regionais.index')
            ->with('success', 'Regional atualizada com sucesso!');
    }

    public function destroy(Regional $regional)
    {
        $regional->delete();

        return redirect()->route('regionais.index')
            ->with('success', 'Regional removida com sucesso!');
    }
}
