<?php

use Illuminate\Support\Facades\Route;

// SPA catch-all: todas las rutas web entregan la vista principal de Vue
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
