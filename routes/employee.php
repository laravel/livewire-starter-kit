<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Employee\Dashboard;
use App\Livewire\Employee\Profile;

/*
|--------------------------------------------------------------------------
| Employee Routes
|--------------------------------------------------------------------------
|
| Rutas para el panel de empleados. Todas las rutas aquí
| requieren autenticación y tienen el prefijo 'employee'.
| Accesible por roles: employee y admin
|
*/

Route::middleware(['auth', 'verified', 'role:employee|admin'])->group(function () {
    
    // Dashboard Employee
    Route::get('/', Dashboard::class)->name('dashboard');

    // Profile - editar info personal
    Route::get('/profile', Profile::class)->name('profile');

    // Settings del empleado
    Route::redirect('settings', 'employee/settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

});
