<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PasanteController;
use App\Http\Controllers\Api\SupervisorController;

Route::apiResource('pasantes', PasanteController::class);
Route::apiResource('supervisores', SupervisorController::class);
