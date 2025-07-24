<?php

namespace App\Console\Commands;

use App\Models\Servidor;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncServidoresToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:servidores-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user accounts for servidores that dont have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $servidores = Servidor::whereNotNull('email')->get();

        $this->info("Found {$servidores->count()} servidores with email");

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($servidores as $servidor) {
            // Check if user already exists
            if (User::where('servidor_id', $servidor->id)->exists()) {
                $skippedCount++;
                continue;
            }

            // Create new user
            User::create([
                'name' => $servidor->nome,
                'email' => $servidor->email,
                'servidor_id' => $servidor->id,
                'password' => Hash::make($servidor->matricula),
            ]);

            $createdCount++;
        }

        $this->info("Operation completed:");
        $this->info(" - {$createdCount} users created");
        $this->info(" - {$skippedCount} servidores already had users");
        
        return Command::SUCCESS;
    }
}