<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Employee Routes
|--------------------------------------------------------------------------
|
| Rutas para el panel de empleados. Todas las rutas aquí
| requieren autenticación y tienen el prefijo 'employee'.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard Employee
    Route::view('/', 'employee.dashboard')->name('dashboard');

    // Settings del empleado
    Route::redirect('settings', 'employee/settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Aquí puedes agregar más rutas específicas para empleados
    // Por ejemplo: ver horarios, registrar asistencia, etc.

});
