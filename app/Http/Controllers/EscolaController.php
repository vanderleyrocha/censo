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
            ->with('cidade.regional')
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
            ->with(['cidade.regional', 'responsavel'])
            ->when($request->cidade_id, fn($query, $cidade_id) => $query->where('cidade_id', $cidade_id))
            ->when($request->dependencia, fn($query, $dependencia) => $query->where('dependencia', $dependencia))
            ->when($request->zona, fn($query, $zona) => $query->where('zona', $zona))
            ->orderBy('nome')
            ->get();

        // Verificar permissões do usuário
        $canView = $request->user()->can('view-school');
        $canEdit = $request->user()->can('edit-regional') ||
            $request->user()->hasAnyRole(['system-admin', 'state-admin', 'regional-admin']);

        // Se não tiver permissão para visualizar, retornar erro
        if (!$canView) {
            abort(403, 'Você não tem permissão para visualizar escolas.');
        }

        $servidor = Servidor::with("regionais")->find($request->user()->servidor_id);

        // return [
        //     'canEdit' => $canEdit,
        //     'userRegionalIds' => $servidor ? $servidor->regionais->pluck('id') : collect(),
        // ];

        $vars = [
            'headerTitle' => 'Atribuir Técnico Responsável pela Escola',
            'escolas' => $escolas,
            'filters' => $request->only(['cidade_id', 'dependencia', 'zona']),
            'cidades' => $cidades,
            'dependencias' => $dependencias,
            'zonas' => $zonas,
            'tecnicos' => $tecnicos,
            'canEdit' => $canEdit,
            'userRegionalIds' => $servidor ? $servidor->regionais->pluck('id') : collect(),
        ];
        return Inertia::render('Escolas/Atribuir', $vars);
    }

    public function atualizarResponsavel(Request $request)
    {
        $request->validate([
            'escolas_ids' => 'required|array',
            'escolas_ids.*' => 'exists:escolas,id',
            'servidor_id' => 'required|exists:servidores,id',
        ]);

        // Verificar se o usuário tem permissão para editar
        if (!$request->user()->can('edit-regional')) {
            abort(403, 'Você não tem permissão para editar vínculos de escolas.');
        }

        // Obter as escolas selecionadas com suas cidades e regionais
        $escolas = Escola::with('cidade.regional')
            ->whereIn('id', $request->escolas_ids)
            ->get();

        // Verificar se o usuário é responsável pela regional de todas as escolas selecionadas
        $servidor = Servidor::with("regionais")->find($request->user()->servidor_id);
        $userRegionalIds = $servidor ? $servidor->regionais->pluck('id') : collect();

        // Só verifica por regional se o usuário NÃO for system-admin ou state-admin
        if (!$request->user()->hasAnyRole(['system-admin', 'state-admin'])) {
            foreach ($escolas as $escola) {
                if (!$userRegionalIds->contains($escola->cidade->regional_id)) {
                    abort(403, 'Você só pode editar escolas vinculadas à sua regional.');
                }
            }
        }

        // Atualizar os responsáveis
        Escola::whereIn('id', $request->escolas_ids)
            ->update(['responsavel_censo' => $request->servidor_id]);

        return redirect()->back()->with('success', 'Responsável atualizado com sucesso!');
    }
}
