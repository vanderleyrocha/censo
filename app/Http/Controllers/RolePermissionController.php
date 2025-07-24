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

    // Criar nova role
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Role criada com sucesso!');
    }

    // Atualizar role
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role atualizada com sucesso!');
    }

    // Deletar role
    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role removida com sucesso!');
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

    public function setAdminPermissions()
    {
        $adminRole = Role::findByName('system_admin');
        $user = User::find(1);
        $user->assignRole($adminRole);
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);
    }
}
