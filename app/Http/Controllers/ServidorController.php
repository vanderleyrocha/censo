<?php

namespace App\Http\Controllers;

use App\Models\Servidor;
use Inertia\Inertia;

class ServidorController extends Controller
{
    // Mostrar detalhes do servidor
    public function show(Servidor $servidor)
    {
        $servidor->load('user.roles');
        return Inertia::render('Servidores/Show', [
            'servidor' => $servidor,
        ]);
    }
}