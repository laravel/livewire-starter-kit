<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
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
    Volt::route('departments', 'departments.department-list')->name('departments.index');
    Volt::route('departments/create', 'departments.department-create')->name('departments.create');
    Volt::route('departments/{department}', 'departments.department-show')->name('departments.show');
    Volt::route('departments/{department}/edit', 'departments.department-edit')->name('departments.edit');
    
    // Rutas para la gestión de áreas
    Volt::route('areas', 'areas.area-list')->name('areas.index');
    Volt::route('areas/create', 'areas.area-create')->name('areas.create');
    Volt::route('areas/{area}', 'areas.area-show')->name('areas.show');
    Volt::route('areas/{area}/edit', 'areas.area-edit')->name('areas.edit');
    
    // Rutas para la gestión de máquinas
    Volt::route('machines', 'machines.machine-list')->name('machines.index');
    Volt::route('machines/create', 'machines.machine-create')->name('machines.create');
    Volt::route('machines/{machine}', 'machines.machine-show')->name('machines.show');
    Volt::route('machines/{machine}/edit', 'machines.machine-edit')->name('machines.edit');
    
    // Rutas para la gestión de semi-automáticos
    Volt::route('semi-automatics', 'semi-automatics.semi-automatic-list')->name('semi-automatics.index');
    Volt::route('semi-automatics/create', 'semi-automatics.semi-automatic-create')->name('semi-automatics.create');
    Volt::route('semi-automatics/{semiAutomatic}', 'semi-automatics.semi-automatic-show')->name('semi-automatics.show');
    Volt::route('semi-automatics/{semiAutomatic}/edit', 'semi-automatics.semi-automatic-edit')->name('semi-automatics.edit');
    
    // Rutas para la gestión de mesas
    Volt::route('tables', 'tables.table-list')->name('tables.index');
    Volt::route('tables/create', 'tables.table-create')->name('tables.create');
    Volt::route('tables/{table}', 'tables.table-show')->name('tables.show');
    Volt::route('tables/{table}/edit', 'tables.table-edit')->name('tables.edit');
});

require __DIR__.'/auth.php';
