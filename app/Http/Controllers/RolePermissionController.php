<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolePermissionController extends Controller
{
    // Listar todas as roles
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'permissions' => Permission::all()->pluck('name')
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Roles/Create', [
            'permissions' => Permission::all()->pluck('name')
        ]);
    }

    // Criar nova role
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'nullable|string|max:255',
            'permissions' => 'array'
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Função criada com sucesso!');
    }

    public function edit(Role $role)
    {
        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::all()->pluck('name')
        ]);
    }

    // Atualizar role
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
            'permissions' => 'array'
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Função atualizada com sucesso!');
    }

    // Deletar role
    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Função removida com sucesso!');
    }

    // Sincronizar permissions para uma role
    public function syncPermissions(Request $request, Role $role)
    {
        $request->validate(['permissions' => 'array']);
        $role->syncPermissions($request->permissions);
        return back()->with('success', 'Permissões atualizadas!');
    }

    // Listar todas as permissions
    public function permissionsIndex()
    {
        $permissions = Permission::all();
        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => $permissions
        ]);
    }

    public function createPermission()
    {
        return Inertia::render('Admin/Permissions/Create');
    }

    public function storePermission(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'description' => 'nullable|string|max:255',
            'guard_name' => 'nullable|string|max:255'
        ]);

        Permission::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'guard_name' => $validated['guard_name'] ?? 'web'
        ]);

        return redirect()->route('permissions')
            ->with('success', 'Permissão criada com sucesso!');
    }

    public function editPermission(Permission $permission)
    {
        return Inertia::render('Admin/Permissions/Edit', [
            'permission' => $permission
        ]);
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
            'description' => 'nullable|string|max:255',
            'guard_name' => 'nullable|string|max:255'
        ]);

        $permission->name = $validated['name'];
        $permission->guard_name = $validated['guard_name'] ?? 'web';
        $permission->description = $validated['description'] ?? null;
        $permission->save();

        return redirect()->route('permissions')
            ->with('success', 'Permissão atualizada com sucesso!');
    }

    public function destroyPermission(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('permissions')
            ->with('success', 'Permissão removida com sucesso!');
    }

    public function setAdminPermissions()
    {
        $roles = Role::all();
        $user = User::find(1);
        foreach ($roles as $role) {
            $user->assignRole($role);
            $permissions = Permission::all();
            $role->syncPermissions($permissions);
        }
    }
}
