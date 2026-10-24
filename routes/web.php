<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PacienteController;

// Rutas Públicas / Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

// Rutas Protegidas
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // MÓDULO 1: Seguridad, Acceso y Auditoría
    Route::middleware('role:Administrador')->group(function () {
        Route::patch('usuarios/{usuario}/toggle', [UsuarioController::class, 'toggle'])->name('usuarios.toggle');
        Route::resource('usuarios', UsuarioController::class)->except(['create', 'edit', 'show']);
        Route::get('roles', [RolController::class, 'index'])->name('roles.index');
        Route::put('roles/{rol}', [RolController::class, 'update'])->name('roles.update');
        Route::get('bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::get('backups/download', [BackupController::class, 'download'])->name('backups.download');
    });

    // MÓDULO 2: Catálogos Clínicos y Personal
    // Especialidades (Admin)
    Route::middleware('role:Administrador')->group(function () {
        Route::resource('especialidades', EspecialidadController::class)->except(['create', 'edit', 'show']);
    });

    // Médicos (Admin y Recepción)
    Route::middleware('role:Administrador,Recepcionista')->group(function () {
        Route::resource('medicos', MedicoController::class)->except(['create', 'edit']);
    });

    // Pacientes (Recepcionista y Admin)
    Route::middleware('role:Administrador,Recepcionista')->group(function () {
        Route::resource('pacientes', PacienteController::class)->except(['create', 'edit']);
    });
});
