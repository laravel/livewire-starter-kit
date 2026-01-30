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

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

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

    // Gestion de Standards
    Route::get('/standards', \App\Livewire\Admin\Standards\StandardList::class)->name('standards.index');
    Route::get('/standards/create', \App\Livewire\Admin\Standards\StandardCreate::class)->name('standards.create');
    Route::get('/standards/{standard}', \App\Livewire\Admin\Standards\StandardShow::class)->name('standards.show');
    Route::get('/standards/{standard}/edit', \App\Livewire\Admin\Standards\StandardEdit::class)->name('standards.edit');

    // Gestion de Estados de Produccion
    Route::get('/production-statuses', \App\Livewire\Admin\ProductionStatuses\ProductionStatusList::class)->name('production-statuses.index');
    Route::get('/production-statuses/create', \App\Livewire\Admin\ProductionStatuses\ProductionStatusCreate::class)->name('production-statuses.create');
    Route::get('/production-statuses/{productionStatus}', \App\Livewire\Admin\ProductionStatuses\ProductionStatusShow::class)->name('production-statuses.show');
    Route::get('/production-statuses/{productionStatus}/edit', \App\Livewire\Admin\ProductionStatuses\ProductionStatusEdit::class)->name('production-statuses.edit');

    // Gestion de Mesas (Tables)
    Route::get('/tables', \App\Livewire\Admin\Tables\TableList::class)->name('tables.index');
    Route::get('/tables/create', \App\Livewire\Admin\Tables\TableCreate::class)->name('tables.create');
    Route::get('/tables/{table}', \App\Livewire\Admin\Tables\TableShow::class)->name('tables.show');
    Route::get('/tables/{table}/edit', \App\Livewire\Admin\Tables\TableEdit::class)->name('tables.edit');

    // Gestion de Semi-Automaticos
    Route::get('/semi-automatics', \App\Livewire\Admin\SemiAutomatics\SemiAutomaticList::class)->name('semi-automatics.index');
    Route::get('/semi-automatics/create', \App\Livewire\Admin\SemiAutomatics\SemiAutomaticCreate::class)->name('semi-automatics.create');
    Route::get('/semi-automatics/{semiAutomatic}', \App\Livewire\Admin\SemiAutomatics\SemiAutomaticShow::class)->name('semi-automatics.show');
    Route::get('/semi-automatics/{semiAutomatic}/edit', \App\Livewire\Admin\SemiAutomatics\SemiAutomaticEdit::class)->name('semi-automatics.edit');

    // Gestion de Maquinas
    Route::get('/machines', \App\Livewire\Admin\Machines\MachineList::class)->name('machines.index');
    Route::get('/machines/create', \App\Livewire\Admin\Machines\MachineCreate::class)->name('machines.create');
    Route::get('/machines/{machine}', \App\Livewire\Admin\Machines\MachineShow::class)->name('machines.show');
    Route::get('/machines/{machine}/edit', \App\Livewire\Admin\Machines\MachineEdit::class)->name('machines.edit');

    // Gestion de Over Times (Tiempo Extra)
    Route::get('/over-times', \App\Livewire\Admin\OverTimes\OverTimeList::class)->name('over-times.index');
    Route::get('/over-times/create', \App\Livewire\Admin\OverTimes\OverTimeCreate::class)->name('over-times.create');
    Route::get('/over-times/{overTime}', \App\Livewire\Admin\OverTimes\OverTimeShow::class)->name('over-times.show');
    Route::get('/over-times/{overTime}/edit', \App\Livewire\Admin\OverTimes\OverTimeEdit::class)->name('over-times.edit');

    // Production Capacity Calculator (Legacy)
    Route::get('/capacity-calculator', \App\Livewire\CapacityCalculator::class)->name('capacity.calculator');
    
    // Capacity Wizard (New 3-step wizard)
    Route::get('/capacity-wizard', \App\Livewire\Admin\CapacityWizard::class)->name('capacity.wizard');

    // Sent Lists Management
    Route::get('/sent-lists', [\App\Http\Controllers\SentListController::class, 'index'])->name('sent-lists.index');
    Route::get('/sent-lists/display', \App\Livewire\Admin\SentLists\ShippingListDisplay::class)->name('sent-lists.display');
    Route::get('/sent-lists/{sentList}', [\App\Http\Controllers\SentListController::class, 'show'])->name('sent-lists.show');
    Route::get('/sent-lists/{sentList}/edit', [\App\Http\Controllers\SentListController::class, 'edit'])->name('sent-lists.edit');
    Route::put('/sent-lists/{sentList}', [\App\Http\Controllers\SentListController::class, 'update'])->name('sent-lists.update');
    Route::delete('/sent-lists/{sentList}', [\App\Http\Controllers\SentListController::class, 'destroy'])->name('sent-lists.destroy');

    // Gestión de Kits
    Route::get('/kits', \App\Livewire\Admin\Kits\KitList::class)->name('kits.index');
    Route::get('/kits/create', \App\Livewire\Admin\Kits\KitCreate::class)->name('kits.create');
    Route::get('/kits/{kit}', \App\Livewire\Admin\Kits\KitShow::class)->name('kits.show');

    // Gestión de Lotes
    Route::get('/lots', \App\Livewire\Admin\Lots\LotList::class)->name('lots.index');
    Route::get('/lots/create', \App\Livewire\Admin\Lots\LotCreate::class)->name('lots.create');
    Route::get('/lots/{lot}', \App\Livewire\Admin\Lots\LotShow::class)->name('lots.show');
    Route::get('/lots/{lot}/edit', \App\Livewire\Admin\Lots\LotEdit::class)->name('lots.edit');

    // Gestión de Empleados
    Route::get('/employees', \App\Livewire\Admin\Employees\EmployeeList::class)->name('employees.index');
    Route::get('/employees/create', \App\Livewire\Admin\Employees\EmployeeCreate::class)->name('employees.create');
    Route::get('/employees/{employee}', \App\Livewire\Admin\Employees\EmployeeShow::class)->name('employees.show');
    Route::get('/employees/{employee}/edit', \App\Livewire\Admin\Employees\EmployeeEdit::class)->name('employees.edit');

});
