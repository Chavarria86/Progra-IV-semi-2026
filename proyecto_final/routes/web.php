<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasanteController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\ViceDecanoController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\AiChatController;

// Rutas de API
Route::prefix('api')->group(function () {
    // Autenticación
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/auth/registro', [AuthController::class, 'registro'])->name('api.registro');
    Route::post('/auth/enviar-codigo', [AuthController::class, 'enviarCodigo'])->name('api.enviarCodigo');
    Route::post('/auth/verificar-codigo', [AuthController::class, 'verificarCodigo'])->name('api.verificarCodigo');
    Route::post('/auth/recuperar', [AuthController::class, 'recuperar'])->name('api.recuperar');
    
    // Rutas de Pasante
    Route::get('/informes/{id}/pdf', [PasanteController::class, 'verPdf']);
    Route::prefix('pasante')->group(function () {
        Route::get('/perfil', [PasanteController::class, 'getPerfil']);
        Route::post('/cv', [PasanteController::class, 'subirCV']);
        Route::get('/cvs', [PasanteController::class, 'getMisCvs']);
        Route::get('/informes', [PasanteController::class, 'getInformes']);
        Route::post('/informes', [PasanteController::class, 'subirInforme']);
        Route::put('/informes/{id}', [PasanteController::class, 'actualizarInforme']);
        Route::delete('/informes/{id}', [PasanteController::class, 'eliminarInforme']);
        Route::get('/vacantes', [PasanteController::class, 'getVacantes']);
        Route::post('/vacantes/{id}/aplicar', [PasanteController::class, 'aplicarVacante']);
        Route::get('/postulaciones', [PasanteController::class, 'getPostulaciones']);
        Route::post('/solicitar-supervisor', [PasanteController::class, 'solicitarSupervisor']);
        Route::get('/mi-supervisor', [PasanteController::class, 'getMiSupervisor']);
    });

    // Rutas de Supervisor
    Route::prefix('supervisor')->group(function () {
        Route::get('/dashboard', [SupervisorController::class, 'getDashboard']);
        // Pasantes
        Route::get('/pasantes', [SupervisorController::class, 'getMisPasantes']);
        Route::post('/asignar', [SupervisorController::class, 'asignarPasante']);
        
        // CVs
        Route::get('/cvs-pendientes', [SupervisorController::class, 'getCvsPendientes']);
        Route::put('/cvs/{id}/validar', [SupervisorController::class, 'validarCV']);
        Route::put('/cvs/{id}/rechazar', [SupervisorController::class, 'rechazarCV']);
        
        // Informes
        Route::get('/informes', [SupervisorController::class, 'getInformesPendientes']);
        Route::get('/informes-revisados', [SupervisorController::class, 'getInformesRevisados']);
        Route::put('/informes/{id}/evaluar', [SupervisorController::class, 'evaluarInforme']);
        Route::put('/informes/{id}/observar', [SupervisorController::class, 'actualizarObservacion']);

        // Solicitudes de Asignación de Pasante
        Route::get('/solicitudes', [SupervisorController::class, 'getSolicitudes']);
        Route::put('/solicitudes/{id}/responder', [SupervisorController::class, 'responderSolicitud']);

        // Postulaciones a Vacantes
        Route::get('/postulaciones', [SupervisorController::class, 'getPostulaciones']);
        Route::put('/postulaciones/{id}/responder', [SupervisorController::class, 'responderPostulacion']);

        // Vacantes (Ver y sugerir)
        Route::get('/vacantes', [SupervisorController::class, 'getVacantes']);
        Route::post('/sugerir-vacante', [SupervisorController::class, 'sugerirVacante']);

        // Recomendaciones
        Route::get('/recomendaciones', [SupervisorController::class, 'getRecomendaciones']);
        Route::post('/recomendaciones', [SupervisorController::class, 'crearRecomendacion']);
    });

    // Rutas de Vice Decano
    Route::prefix('vicedecano')->group(function () {
        Route::get('/dashboard', [ViceDecanoController::class, 'getDashboard']);
        Route::get('/informes/finales', [ViceDecanoController::class, 'getInformesFinales']);
        Route::put('/informes/{id}/evaluar', [ViceDecanoController::class, 'evaluarInforme']);
        Route::post('/vacantes', [ViceDecanoController::class, 'crearVacante']);
        Route::get('/asignaciones-data', [ViceDecanoController::class, 'getAsignacionesData']);
        Route::post('/asignar-supervisor', [ViceDecanoController::class, 'asignarSupervisor']);
        Route::get('/postulaciones', [ViceDecanoController::class, 'getPostulaciones']);
        Route::put('/postulaciones/{id}/evaluar', [ViceDecanoController::class, 'evaluarPostulacion']);
    });

    // ── Rutas de Curriculum Vitae ──────────────────────────────────────────────
    // Guardar / actualizar CV (solo el propio usuario lo hace desde el wizard)
    Route::post('/cv/guardar', [CvController::class, 'guardar']);
    // Obtener todos los CVs de un usuario específico
    Route::get('/cv/{usuarioId}', [CvController::class, 'obtener']);
    // Eliminar un CV específico por su cv ID
    Route::delete('/cv/{cvId}', [CvController::class, 'eliminar']);

    // ── Rutas de Asistente de IA (Chatbot) ─────────────────────────────────────
    Route::prefix('ai')->group(function () {
        Route::get('/chats', [AiChatController::class, 'getChats']);
        Route::post('/chats', [AiChatController::class, 'crearChat']);
        Route::get('/chats/{id}/mensajes', [AiChatController::class, 'getMensajes']);
        Route::post('/chats/{id}/enviar', [AiChatController::class, 'enviarMensaje']);
        Route::get('/adjuntos-disponibles', [AiChatController::class, 'getAdjuntosDisponibles']);
    });
});

// Ruta para la vista de login (Blade) - Si prefieren usar blade en vez de Vue
Route::get('/login', function () {
    return view('login');
})->name('login');

// Ruta principal para la landing page (Blade)
Route::get('/', function () {
    return view('welcome');
});

// Ruta de respaldo (Fallback) para servir archivos de almacenamiento público en entornos con problemas de enlaces simbólicos (ej. OneDrive)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path("app/public/{$path}");
    if (file_exists($filePath)) {
        return response()->file($filePath);
    }
    abort(404);
})->where('path', '.*');

// Ruta "Catch-all" para la aplicación Vue (Dashboards)
Route::get('/dashboard/{any}', function () {
    // Retornamos una vista especial blade que contendrá el div #app para Vue
    return view('app');
})->where('any', '.*');
