<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HildervaldoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'hider13@gmail.com'],
            [
                'name' => 'Hildervaldo Dourado Paiva',
                'password' => Hash::make('703703'),
                'servidor_id' => 18,
                'email_verified_at' => now(),
            ]
        );
    }
}