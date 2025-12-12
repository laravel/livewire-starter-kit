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
    Volt::route('settings/profile', 'admin.settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'admin.settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'admin.settings.appearance')->name('settings.appearance');

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

    // Gestión de descansos
    Route::get('/break-times', \App\Livewire\Admin\BreakTimes\BreakTimeList::class)->name('break-times.index');
    Route::get('/break-times/create', \App\Livewire\Admin\BreakTimes\BreakTimeCreate::class)->name('break-times.create');
    Route::get('/break-times/{breakTime}', \App\Livewire\Admin\BreakTimes\BreakTimeShow::class)->name('break-times.show');
    Route::get('/break-times/{breakTime}/edit', \App\Livewire\Admin\BreakTimes\BreakTimeEdit::class)->name('break-times.edit');

    // Gestión de estados de Work Orders
    Route::get('/statuses-wo', \App\Livewire\Admin\StatusesWO\StatusWOList::class)->name('statuses-wo.index');
    Route::get('/statuses-wo/create', \App\Livewire\Admin\StatusesWO\StatusWOCreate::class)->name('statuses-wo.create');
    Route::get('/statuses-wo/{statusWO}/edit', \App\Livewire\Admin\StatusesWO\StatusWOEdit::class)->name('statuses-wo.edit');

    // Gestión de precios
    Route::get('/prices', \App\Livewire\Admin\Prices\PriceList::class)->name('prices.index');
    Route::get('/prices/create', \App\Livewire\Admin\Prices\PriceCreate::class)->name('prices.create');
    Route::get('/prices/{price}/edit', \App\Livewire\Admin\Prices\PriceEdit::class)->name('prices.edit');

    // Gestión de partes
    Route::get('/parts', \App\Livewire\Admin\Parts\PartList::class)->name('parts.index');
    Route::get('/parts/create', \App\Livewire\Admin\Parts\PartCreate::class)->name('parts.create');
    Route::get('/parts/{part}', \App\Livewire\Admin\Parts\PartShow::class)->name('parts.show');
    Route::get('/parts/{part}/edit', \App\Livewire\Admin\Parts\PartEdit::class)->name('parts.edit');

    // Gestión de órdenes de compra (Purchase Orders)
    Route::get('/purchase-orders', \App\Livewire\Admin\PurchaseOrders\POList::class)->name('purchase-orders.index');
    Route::get('/purchase-orders/create', \App\Livewire\Admin\PurchaseOrders\POCreate::class)->name('purchase-orders.create');
    Route::get('/purchase-orders/{purchaseOrder}', \App\Livewire\Admin\PurchaseOrders\POShow::class)->name('purchase-orders.show');
    Route::get('/purchase-orders/{purchaseOrder}/edit', \App\Livewire\Admin\PurchaseOrders\POEdit::class)->name('purchase-orders.edit');

    // Gestión de Work Orders
    Route::get('/work-orders', \App\Livewire\Admin\WorkOrders\WOList::class)->name('work-orders.index');
    Route::get('/work-orders/{workOrder}', \App\Livewire\Admin\WorkOrders\WOShow::class)->name('work-orders.show');
    Route::get('/work-orders/{workOrder}/edit', \App\Livewire\Admin\WorkOrders\WOEdit::class)->name('work-orders.edit');

    // gestion de Partes
    /* Route::get('/parts', \App\Livewire\Admin\Parts\PartList::class)->name('parts.index');
    Route::get('/parts/create', \App\Livewire\Admin\Parts\PartCreate::class)->name('parts.create');
    Route::get('/parts/{part}', \App\Livewire\Admin\Parts\PartShow::class)->name('parts.show');
    Route::get('/parts/{part}/edit', \App\Livewire\Admin\Parts\PartEdit::class)->name('parts.edit'); */

});
