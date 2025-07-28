<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{

    public function index()
    {
        $users = User::with(['servidor', 'roles'])->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'servidor_id' => $user->servidor_id,
                    'roles' => $user->roles->pluck('name')->toArray(),
                ];
            })
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    public function show(User $user)
    {
        $user->load('roles');

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'roles' => $user->roles,
                'servidor_id' => $user->servidor_id,
            ],
        ]);
    }

    public function editRoles(User $user)
    {
        $roles = Role::all();

        return Inertia::render('Admin/Users/EditRoles', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->roles,
            ],
            'roles' => $roles,
        ]);
    }

public function updateRoles(Request $request, User $user)
{
    $request->validate([
        'roles' => 'array',
        'roles.*' => 'exists:roles,id',
    ]);

    // Impedir que um usuário remova todos os seus próprios roles
    if ($user->id === Auth::user()->id && empty($request->roles)) {
        return back()->with('error', 'Você não pode remover todas as suas próprias funções.');
    }

    $user->syncRoles($request->roles);

    return redirect()->route('users.index')
        ->with('success', 'Funções do usuário atualizadas com sucesso!');
}

    // Deletar role
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuário removido com sucesso!');
    }
}
