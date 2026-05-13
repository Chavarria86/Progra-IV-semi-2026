<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasanteController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\ViceDecanoController;
use App\Http\Controllers\CvController;

// Rutas de API
Route::prefix('api')->group(function () {
    // Autenticación
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/auth/registro', [AuthController::class, 'registro'])->name('api.registro');
    Route::post('/auth/enviar-codigo', [AuthController::class, 'enviarCodigo'])->name('api.enviarCodigo');
    Route::post('/auth/recuperar', [AuthController::class, 'recuperar'])->name('api.recuperar');
    
    // Rutas de Pasante
    Route::prefix('pasante')->group(function () {
        Route::get('/perfil', [PasanteController::class, 'getPerfil']);
        Route::post('/cv', [PasanteController::class, 'subirCV']);
        Route::post('/informes', [PasanteController::class, 'subirInforme']);
        Route::get('/vacantes', [PasanteController::class, 'getVacantes']);
    });

    // Rutas de Supervisor
    Route::prefix('supervisor')->group(function () {
        Route::get('/pasantes', [SupervisorController::class, 'getPasantes']);
        Route::put('/cvs/{id}/validar', [SupervisorController::class, 'validarCV']);
        Route::put('/cvs/{id}/rechazar', [SupervisorController::class, 'rechazarCV']);
        Route::post('/asignar', [SupervisorController::class, 'asignarPasante']);
    });

    // Rutas de Vice Decano
    Route::prefix('vicedecano')->group(function () {
        Route::get('/dashboard', [ViceDecanoController::class, 'getDashboard']);
        Route::get('/informes/finales', [ViceDecanoController::class, 'getInformesFinales']);
        Route::put('/informes/{id}/evaluar', [ViceDecanoController::class, 'evaluarInforme']);
    });

    // ── Rutas de Curriculum Vitae ──────────────────────────────────────────────
    // Guardar / actualizar CV (solo el propio usuario lo hace desde el wizard)
    Route::post('/cv/guardar', [CvController::class, 'guardar']);
    // Obtener CV de un usuario específico
    Route::get('/cv/{usuarioId}', [CvController::class, 'obtener']);
    // Eliminar CV de un usuario
    Route::delete('/cv/{usuarioId}', [CvController::class, 'eliminar']);
});

// Ruta para la vista de login (Blade) - Si prefieren usar blade en vez de Vue
Route::get('/login', function () {
    return view('login');
})->name('login');

// Ruta principal para la landing page (Blade)
Route::get('/', function () {
    return view('welcome');
});

// Ruta "Catch-all" para la aplicación Vue (Dashboards)
Route::get('/dashboard/{any}', function () {
    // Retornamos una vista especial blade que contendrá el div #app para Vue
    return view('app');
})->where('any', '.*');
