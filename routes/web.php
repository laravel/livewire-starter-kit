<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\UserController;
use App\Http\Controllers\POController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ShiftController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

/* Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Rutas para la gestión de usuarios
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');

    // Rutas para la gestión de roles
    Volt::route('roles', 'roles.role-list')->name('roles.index');
    Volt::route('roles/create', 'roles.role-create')->name('roles.create');
    Volt::route('roles/{role}/edit', 'roles.role-edit')->name('roles.edit');

    // Rutas para la gestión de permisos
    Volt::route('permissions', 'permissions.permission-list')->name('permissions.index');
    Volt::route('permissions/create', 'permissions.permission-create')->name('permissions.create');
    Volt::route('permissions/{permission}/edit', 'permissions.permission-edit')->name('permissions.edit');

    // Rutas para la gestión de departamentos
    Route::get('/departments', [\App\Http\Controllers\DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/create', [\App\Http\Controllers\DepartmentController::class, 'create'])->name('departments.create');
    Route::get('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'show'])->name('departments.show');
    Route::get('/departments/{department}/edit', [\App\Http\Controllers\DepartmentController::class, 'edit'])->name('departments.edit');

    // Rutas para la gestión de áreas
    Route::get('/areas', [\App\Http\Controllers\AreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/create', [\App\Http\Controllers\AreaController::class, 'create'])->name('areas.create');
    Route::get('/areas/{area}', [\App\Http\Controllers\AreaController::class, 'show'])->name('areas.show');
    Route::get('/areas/{area}/edit', [\App\Http\Controllers\AreaController::class, 'edit'])->name('areas.edit');

    //Rutas para la gestion de Holidays
    Route::resource('/holidays', HolidayController::class)->only(['index','create','show','edit']);

    //Rutas para gestionar Shifts
    Route::resource('/shifts', ShiftController::class)->only(['index','create','show','edit']);

});*/

require __DIR__.'/auth.php';
