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
        $tecnicos = Servidor::whereIn('funcao', ['TÉCNICO', 'ASSESSOR TÉCNICO', 'Coordenador Municipal do Censo Escolar'])->orderBy('nome')->get(['id', 'nome']);

        $escolas = Escola::query()
            ->with(['cidade.regional', 'responsavel'])
            ->when($request->cidade_id, fn($query, $cidade_id) => $query->where('cidade_id', $cidade_id))
            ->when($request->dependencia, fn($query, $dependencia) => $query->where('dependencia', $dependencia))
            ->when($request->zona, fn($query, $zona) => $query->where('zona', $zona))
            ->orderBy('nome')
            ->get();

        // Verificar permissões do usuário
        $canView = $request->user()->can('view-school') ||
            $request->user()->hasAnyRole(['system-admin', 'state-admin', 'regiao-admin', 'regional-admin']);
        $canEdit = $request->user()->can('edit-regional') ||
            $request->user()->hasAnyRole(['system-admin', 'state-admin', 'regiao-admin', 'regional-admin']);

        if (!$canView) {
            abort(403, 'Você não tem permissão para visualizar escolas.');
        }

        $servidor = Servidor::with(["regioes", "regionais"])->find($request->user()->servidor_id);

        return Inertia::render('Escolas/Atribuir', [
            'headerTitle' => 'Atribuir Técnico Responsável pela Escola',
            'escolas' => $escolas,
            'filters' => $request->only(['cidade_id', 'dependencia', 'zona']),
            'cidades' => $cidades,
            'dependencias' => $dependencias,
            'zonas' => $zonas,
            'tecnicos' => $tecnicos,
            'canEdit' => $canEdit,
            'userRegiaoIds' => $servidor ? $servidor->regioes->pluck('id') : collect(),
            'userRegionalIds' => $servidor ? $servidor->regionais->pluck('id') : collect(),
            'userRoles' => $request->user()->roles->pluck('name'),
        ]);
    }

    public function atualizarResponsavel(Request $request)
    {
        $request->validate([
            'escolas_ids' => 'required|array',
            'escolas_ids.*' => 'exists:escolas,id',
            'servidor_id' => 'required|exists:servidores,id',
        ]);

        $user = $request->user();

        // Verificar se o usuário tem permissão para editar
        if (!$user->can('edit-regional') && !$user->hasAnyRole(['system-admin', 'state-admin', 'regiao-admin', 'regional-admin'])) {
            abort(403, 'Você não tem permissão para editar vínculos de escolas.');
        }

        // Se for system-admin ou state-admin, pode editar qualquer escola
        if ($user->hasAnyRole(['system-admin', 'state-admin'])) {
            Escola::whereIn('id', $request->escolas_ids)
                ->update(['responsavel_censo' => $request->servidor_id]);
                
            return redirect()->back()->with('success', 'Responsável atualizado com sucesso!');
        }

        // Para regiao-admin e regional-admin, verificar se as escolas pertencem às suas regionais/regiões
        $escolas = Escola::with('cidade.regional')
            ->whereIn('id', $request->escolas_ids)
            ->get();

        $servidor = Servidor::with(["regioes", "regionais"])->find($user->servidor_id);
        $userRegiaoIds = $servidor ? $servidor->regioes->pluck('id') : collect();
        $userRegionalIds = $servidor ? $servidor->regionais->pluck('id') : collect();

        $validSchoolIds = [];
        foreach ($escolas as $escola) {
            if ($user->hasRole('regiao-admin')) {
                // Verifica se a escola pertence a uma regional da regiao do usuário
                if ($userRegiaoIds->contains($escola->cidade->regional->regiao_id)) {
                    $validSchoolIds[] = $escola->id;
                }
            } elseif ($user->hasRole('regional-admin')) {
                // Verifica se a escola pertence diretamente à regional do usuário
                if ($userRegionalIds->contains($escola->cidade->regional_id)) {
                    $validSchoolIds[] = $escola->id;
                }
            }
        }

        // Verificar se todas as escolas selecionadas são válidas
        if (count($validSchoolIds) !== count($request->escolas_ids)) {
            abort(403, 'Você só pode editar escolas vinculadas à sua regional/região.');
        }

        // Atualizar os responsáveis
        Escola::whereIn('id', $validSchoolIds)
            ->update(['responsavel_censo' => $request->servidor_id]);

        return redirect()->back()->with('success', 'Responsável atualizado com sucesso!');
    }
}