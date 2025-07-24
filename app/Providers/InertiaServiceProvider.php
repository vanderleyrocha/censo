<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class InertiaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Compartilha variáveis com todas as views Inertia
        Inertia::share([
            'app' => [
                'name' => config('app.name'),
            ],
        ]);

        // Define o diretório padrão das páginas (opcional)
        Inertia::setRootView('app'); // Assume que você tem uma view Blade chamada 'app.blade.php'
    }
}