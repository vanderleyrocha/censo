<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/versions', function (Request $request) {
    $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);
    $composerLock = json_decode(file_get_contents(base_path('composer.lock')), true);

    // Encontrar versões dos pacotes no composer.lock
    $getPackageVersion = function ($packageName) use ($composerLock) {
        foreach ($composerLock['packages'] as $package) {
            if ($package['name'] === $packageName) {
                return $package['version'];
            }
        }
        return 'Não encontrado';
    };

    return response()->json([
        'php' => PHP_VERSION,
        'laravel' => App::version(),
        'phpspreadsheet' => $getPackageVersion('phpoffice/phpspreadsheet'),
        'laravel_permission' => $getPackageVersion('spatie/laravel-permission'),
        'inertia' => $getPackageVersion('inertiajs/inertia-laravel'),
        'react' => '18.x', // Versão padrão do React com Laravel Breeze
        'environment' => App::environment(),
        'timestamp' => date('d/m/Y H:i:s'),
    ]);
});
