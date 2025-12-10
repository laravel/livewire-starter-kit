<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Rutas para el panel de administración. Todas las rutas aquí
| requieren autenticación y tienen el prefijo 'admin'.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard Admin
    Route::view('/', 'admin.dashboard')->name('dashboard');

    // Settings
    Route::redirect('settings', 'admin/settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Gestión de usuarios
    Route::get('/users', \App\Livewire\Admin\Users\UserList::class)->name('users.index');
    Route::get('/users/create', \App\Livewire\Admin\Users\UserCreate::class)->name('users.create');
    Route::get('/users/{user}/edit', \App\Livewire\Admin\Users\UserEdit::class)->name('users.edit');

    // Gestión de roles
    Route::get('/roles', \App\Livewire\Admin\Roles\RoleList::class)->name('roles.index');
    Route::get('/roles/create', \App\Livewire\Admin\Roles\RoleCreate::class)->name('roles.create');
    Route::get('/roles/{role}/edit', \App\Livewire\Admin\Roles\RoleEdit::class)->name('roles.edit');

    // Gestión de permisos
    Route::get('/permissions', \App\Livewire\Admin\Permissions\PermissionList::class)->name('permissions.index');
    Route::get('/permissions/create', \App\Livewire\Admin\Permissions\PermissionCreate::class)->name('permissions.create');
    Route::get('/permissions/{permission}/edit', \App\Livewire\Admin\Permissions\PermissionEdit::class)->name('permissions.edit');

    // Gestión de departamentos
    Route::get('/departments', \App\Livewire\Admin\Departments\DepartmentList::class)->name('departments.index');
    Route::get('/departments/create', \App\Livewire\Admin\Departments\DepartmentCreate::class)->name('departments.create');
    Route::get('/departments/{department}', \App\Livewire\Admin\Departments\DepartmentShow::class)->name('departments.show');
    Route::get('/departments/{department}/edit', \App\Livewire\Admin\Departments\DepartmentEdit::class)->name('departments.edit');

    // Gestión de áreas
    Route::get('/areas', \App\Livewire\Admin\Areas\AreaList::class)->name('areas.index');
    Route::get('/areas/create', \App\Livewire\Admin\Areas\AreaCreate::class)->name('areas.create');
    Route::get('/areas/{area}', \App\Livewire\Admin\Areas\AreaShow::class)->name('areas.show');
    Route::get('/areas/{area}/edit', \App\Livewire\Admin\Areas\AreaEdit::class)->name('areas.edit');

    // Gestión de días festivos
    Route::get('/holidays', \App\Livewire\Admin\Holidays\Holidays::class)->name('holidays.index');
    Route::get('/holidays/create', \App\Livewire\Admin\Holidays\HolidayCreate::class)->name('holidays.create');
    Route::get('/holidays/{holiday}', \App\Livewire\Admin\Holidays\HolidayShow::class)->name('holidays.show');
    Route::get('/holidays/{holiday}/edit', \App\Livewire\Admin\Holidays\HolidayEdit::class)->name('holidays.edit');

    // Gestión de turnos
    Route::get('/shifts', \App\Livewire\Admin\Shifts\ShiftList::class)->name('shifts.index');
    Route::get('/shifts/create', \App\Livewire\Admin\Shifts\ShiftCreate::class)->name('shifts.create');
    Route::get('/shifts/{shift}', \App\Livewire\Admin\Shifts\ShiftShow::class)->name('shifts.show');
    Route::get('/shifts/{shift}/edit', \App\Livewire\Admin\Shifts\ShiftEdit::class)->name('shifts.edit');

});
