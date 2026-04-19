<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\InscripcionController;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Rutas visuales (UI)
Route::resource('alumnos', AlumnoController::class)->except(['store', 'update', 'destroy']);
Route::resource('docentes', DocenteController::class)->except(['store', 'update', 'destroy']);
Route::resource('materias', MateriaController::class)->except(['store', 'update', 'destroy']);
Route::resource('matriculas', MatriculaController::class)->except(['store', 'update', 'destroy']);
Route::resource('inscripciones', InscripcionController::class)->except(['store', 'update', 'destroy']);

// Rutas API PWA (sin CSRF param testing/PWA migración)

// --- API ALUMNOS ---
Route::post('/api/alumnos', [AlumnoController::class, 'store'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::put('/api/alumnos/{id}', [AlumnoController::class, 'update'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::delete('/api/alumnos/{id}', [AlumnoController::class, 'destroy'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::get('/api/alumnos', [AlumnoController::class, 'index']);

// --- API DOCENTES ---
Route::post('/api/docentes', [DocenteController::class, 'store'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::put('/api/docentes/{id}', [DocenteController::class, 'update'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::delete('/api/docentes/{id}', [DocenteController::class, 'destroy'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::get('/api/docentes', [DocenteController::class, 'index']);

// --- API MATERIAS ---
Route::post('/api/materias', [MateriaController::class, 'store'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::put('/api/materias/{id}', [MateriaController::class, 'update'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::delete('/api/materias/{id}', [MateriaController::class, 'destroy'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::get('/api/materias', [MateriaController::class, 'index']);

// --- API MATRICULAS ---
Route::post('/api/matriculas', [MatriculaController::class, 'store'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::put('/api/matriculas/{id}', [MatriculaController::class, 'update'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::delete('/api/matriculas/{id}', [MatriculaController::class, 'destroy'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::get('/api/matriculas', [MatriculaController::class, 'index']);

// --- API INSCRIPCIONES ---
Route::post('/api/inscripciones', [InscripcionController::class, 'store'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::put('/api/inscripciones/{id}', [InscripcionController::class, 'update'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::delete('/api/inscripciones/{id}', [InscripcionController::class, 'destroy'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
Route::get('/api/inscripciones', [InscripcionController::class, 'index']);

