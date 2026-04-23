<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\InscripcionController;

// --- API ALUMNOS ---
Route::get('/alumnos',        [AlumnoController::class, 'index']);
Route::post('/alumnos',       [AlumnoController::class, 'store']);
Route::put('/alumnos/{id}',   [AlumnoController::class, 'update']);
Route::delete('/alumnos/{id}',[AlumnoController::class, 'destroy']);

// --- API DOCENTES ---
Route::get('/docentes',        [DocenteController::class, 'index']);
Route::post('/docentes',       [DocenteController::class, 'store']);
Route::put('/docentes/{id}',   [DocenteController::class, 'update']);
Route::delete('/docentes/{id}',[DocenteController::class, 'destroy']);

// --- API MATERIAS ---
Route::get('/materias',        [MateriaController::class, 'index']);
Route::post('/materias',       [MateriaController::class, 'store']);
Route::put('/materias/{id}',   [MateriaController::class, 'update']);
Route::delete('/materias/{id}',[MateriaController::class, 'destroy']);

// --- API MATRICULAS ---
Route::get('/matriculas',        [MatriculaController::class, 'index']);
Route::post('/matriculas',       [MatriculaController::class, 'store']);
Route::put('/matriculas/{id}',   [MatriculaController::class, 'update']);
Route::delete('/matriculas/{id}',[MatriculaController::class, 'destroy']);

// --- API INSCRIPCIONES ---
Route::get('/inscripciones',        [InscripcionController::class, 'index']);
Route::post('/inscripciones',       [InscripcionController::class, 'store']);
Route::put('/inscripciones/{id}',   [InscripcionController::class, 'update']);
Route::delete('/inscripciones/{id}',[InscripcionController::class, 'destroy']);
