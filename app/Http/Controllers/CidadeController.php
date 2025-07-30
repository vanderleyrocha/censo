<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Regional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CidadeController extends Controller
{
    public function index(Request $request)
    {
        $query = Cidade::with(['estado', 'regional'])
            ->orderBy('nome');

        // Filtros
        if ($request->filled('estado_id')) {
            $query->where('estado_id', $request->estado_id);
        }

        if ($request->filled('regional_id')) {
            $query->where('regional_id', $request->regional_id);
        }

        $cidades = $query->paginate(25)->withQueryString();

        return Inertia::render('Cidades/Index', [
            'cidades' => $cidades,
            'estados' => Estado::orderBy('nome')->get(),
            'regionais' => Regional::orderBy('nome')->get(),
            'filters' => $request->only(['estado_id', 'regional_id'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Cidades/Create', [
            'estados' => Estado::orderBy('nome')->get(),
            'regionais' => Regional::orderBy('nome')->get()
        ]);
    }


    public function edit(Cidade $cidade)
    {
        return Inertia::render('Cidades/Edit', [
            'cidade' => $cidade,
            'estados' => Estado::orderBy('nome')->get(),
            'regionais' => Regional::orderBy('nome')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'estado_id' => 'required|exists:estados,id',
            'regional_id' => 'required|exists:regionais,id',
            'ibge' => [
                'nullable',
                'string',
                'size:7',
                'regex:/^\d{7}$/',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->estado_id) {
                        $estado = Estado::find($request->estado_id);
                        if ($estado && substr($value, 0, 2) != $estado->ibge) {
                            $fail('Os dois primeiros dígitos do código IBGE devem corresponder ao código do estado selecionado.');
                        }
                    }
                },
            ],
        ]);

        // Remove formatação se existir
        $ibge = str_replace(['.', '-'], '', $request->ibge);
        $request->merge(['ibge' => $ibge]);

        Cidade::create($request->all());

        return redirect()->route('cidades.index')
            ->with('success', 'Cidade criada com sucesso!');
    }

    public function update(Request $request, Cidade $cidade)
    {
        // Remove formatação ANTES da validação
        $ibgeUnformatted = $request->ibge ? str_replace(['.', '-'], '', $request->ibge) : null;
        $request->merge(['ibge' => $ibgeUnformatted]);

        Log::info('Atualizando cidade: ' . $cidade->id, $request->all());

        $request->validate([
            'nome' => 'required|string|max:255',
            'estado_id' => 'required|exists:estados,id',
            'regional_id' => 'required|exists:regionais,id',
            'ibge' => [
                'nullable',
                'string',
                'size:7',
                'regex:/^\d{7}$/',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->estado_id) {
                        $estado = Estado::find($request->estado_id);
                        if ($estado && substr($value, 0, 2) != $estado->ibge) {
                            $fail('Os dois primeiros dígitos do código IBGE devem corresponder ao código do estado selecionado.');
                        }
                    }
                },
            ],
        ]);

        $cidade->update($request->all());

        // Obter os filtros atuais da requisição
        $filters = $request->only(['estado_id', 'regional_id']);

        // Redirecionar mantendo os filtros
        return redirect()->route('cidades.index', $filters)
            ->with('success', 'Cidade atualizada com sucesso!');
    }


    public function destroy(Cidade $cidade)
    {
        $cidade->delete();

        return redirect()->route('cidades.index')
            ->with('success', 'Cidade removida com sucesso!');
    }
}
