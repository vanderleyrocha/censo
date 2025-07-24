<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'admin' => 'Administrador do sistema',
            'state' => 'Administrador Estadual',
            'regional' => 'Administrador Regional',
            'school' => 'Técnico Censo Escolar'
        ];

        foreach ($roles as $key => $name) {
            Role::firstOrCreate([
                'name' => $key,
                'guard_name' => 'web'
            ], [
                'name' => $name
            ]);
        }
    }
}