<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rutas públicas y redirecciones principales.
| Las rutas de admin y employee están en sus archivos respectivos.
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Redirigir dashboard general según el rol del usuario
Route::get('/dashboard', function () {
    // Por defecto redirige a admin, puedes agregar lógica de roles aquí
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
