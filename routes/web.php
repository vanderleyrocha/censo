<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\EscolaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rotas Públicas
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::middleware(['auth', 'verified', 'role:system-admin'])->group(function () {
    Route::resource('roles', RolePermissionController::class)->except(['show']);
    Route::post('roles/{role}/permissions', [RolePermissionController::class, 'syncPermissions']);
    Route::get('permissions', [RolePermissionController::class, 'permissionsIndex'])->name('permissions');

    Route::resource('users', UserController::class);

});

// Rotas de Autenticação para convidados
Route::middleware('guest')->group(function () {

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// Rotas Protegidas (após login)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard principal
    Route::get('/admin/permissions/set', [RolePermissionController::class, 'setAdminPermissions'])->name('admin.permissions.set');

    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/escola/tecnico', [HomeController::class, 'escolas_por_tecnico'])->name('escolas.tecnico');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Adicione esta rota no grupo de rotas protegidas

    Route::get('/escola/index', [EscolaController::class, 'index'])->name('escolas.index');
    Route::get('/escolas/atribuir', [EscolaController::class, 'atribuir'])->name('escolas.atribuir');
    Route::post('/escolas/atualizar-responsavel', [EscolaController::class, 'atualizarResponsavel'])->name('escolas.atualizar-responsavel');


    // Perfil do usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Confirmação de senha
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
});

// Rotas de verificação de email
Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [EmailVerificationPromptController::class, '__invoke'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
});
