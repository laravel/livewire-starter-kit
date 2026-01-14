<?php

use Livewire\Volt\Component;
use App\Models\Shift;
use App\Models\User;

?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($shift->name); ?></h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Detalles del turno de trabajo
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <div class="flex space-x-2">
                        <a href="<?php echo e(route('admin.shifts.edit', $shift)); ?>"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Editar
                        </a>
                        <a href="<?php echo e(route('admin.shifts.index')); ?>"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles del Turno -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    <?php echo e($shift->name); ?>

                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Informacion detallada del turno
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Nombre del Turno
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <?php echo e($shift->name); ?>

                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Horario
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <?php echo e(\Carbon\Carbon::parse($shift->start_time)->format('H:i')); ?> -
                                <?php echo e(\Carbon\Carbon::parse($shift->end_time)->format('H:i')); ?>

                            </div>
                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Estado
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shift->active): ?>
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Activo
                                </span>
                            <?php else: ?>
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Inactivo
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Comentarios
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <?php echo e($shift->comments ?? 'Sin comentarios'); ?>

                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Fecha de Creacion
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <?php echo e($shift->created_at->format('d/m/Y H:i')); ?>

                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Ultima Actualizacion
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <?php echo e($shift->updated_at->format('d/m/Y H:i')); ?>

                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Informacion de Horas del Turno -->
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informacion de Horas</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Horas Totales del Turno -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-blue-500">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Horas Totales
                            </dt>
                            <dd class="mt-1 text-3xl font-semibold text-blue-600 dark:text-blue-400">
                                <?php echo e($shift->formatted_total_hours); ?>

                            </dd>
                            <dd class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                <?php echo e(\Carbon\Carbon::parse($shift->start_time)->format('H:i')); ?> - <?php echo e(\Carbon\Carbon::parse($shift->end_time)->format('H:i')); ?>

                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Tiempo de Descansos -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-orange-500">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                                <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Tiempo de Descansos
                            </dt>
                            <dd class="mt-1 text-3xl font-semibold text-orange-600 dark:text-orange-400">
                                <?php echo e($shift->formatted_break_time); ?>

                            </dd>
                            <dd class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                <?php echo e($shift->BreakTimes->where('active', true)->count()); ?> descanso(s) activo(s)
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Horas Netas Laborables -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-green-500">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Horas Laborables Netas
                            </dt>
                            <dd class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-400">
                                <?php echo e($shift->formatted_net_working_hours); ?>

                            </dd>
                            <dd class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                Tiempo efectivo de trabajo
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Detalle del Calculo -->
            <div class="mt-4 bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Detalle del Calculo</h4>
                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <p>
                        <span class="font-medium">Turno completo:</span>
                        <?php echo e($shift->formatted_total_hours); ?>

                        (<?php echo e($shift->total_minutes); ?> minutos)
                    </p>
                    <p>
                        <span class="font-medium">(-) Descansos:</span>
                        <?php echo e($shift->formatted_break_time); ?>

                        (<?php echo e($shift->total_break_minutes); ?> minutos)
                    </p>
                    <p class="pt-1 border-t border-gray-200 dark:border-gray-700">
                        <span class="font-medium">(=) Tiempo neto:</span>
                        <?php echo e($shift->formatted_net_working_hours); ?>

                        (<?php echo e($shift->net_working_minutes); ?> minutos)
                    </p>
                </div>
            </div>
        </div>

        <!-- Estadisticas de Empleados -->
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Estadisticas de Empleados</h3>

            <!-- Estadisticas Globales del Sistema -->
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Estadisticas Globales</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Total Empleados Activos (Global) -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-green-500">
                        <div class="px-4 py-5 sm:p-6">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Empleados Activos (Sistema)
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold <?php echo e(is_null($globalStats['total_active']) ? 'text-gray-400 dark:text-gray-500' : 'text-green-600 dark:text-green-400'); ?>">
                                    <?php echo e($globalStats['total_active'] ?? 'N/A'); ?>

                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Total Empleados Inactivos (Global) -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-red-500">
                        <div class="px-4 py-5 sm:p-6">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Empleados Inactivos (Sistema)
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold <?php echo e(is_null($globalStats['total_inactive']) ? 'text-gray-400 dark:text-gray-500' : 'text-red-600 dark:text-red-400'); ?>">
                                    <?php echo e($globalStats['total_inactive'] ?? 'N/A'); ?>

                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Total General (Global) -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-blue-500">
                        <div class="px-4 py-5 sm:p-6">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Total Empleados (Sistema)
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold <?php echo e(is_null($globalStats['total_all']) ? 'text-gray-400 dark:text-gray-500' : 'text-blue-600 dark:text-blue-400'); ?>">
                                    <?php echo e($globalStats['total_all'] ?? 'N/A'); ?>

                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadisticas del Turno Actual -->
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Estadisticas de este Turno: <?php echo e($shift->name); ?></h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Empleados Asignados al Turno -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-indigo-500">
                        <div class="px-4 py-5 sm:p-6">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    Total Asignados
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold <?php echo e(is_null($shiftStats['total']) ? 'text-gray-400 dark:text-gray-500' : 'text-indigo-600 dark:text-indigo-400'); ?>">
                                    <?php echo e($shiftStats['total'] ?? 'N/A'); ?>

                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Empleados Activos en el Turno -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-emerald-500">
                        <div class="px-4 py-5 sm:p-6">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Activos en Turno
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold <?php echo e(is_null($shiftStats['active']) ? 'text-gray-400 dark:text-gray-500' : 'text-emerald-600 dark:text-emerald-400'); ?>">
                                    <?php echo e($shiftStats['active'] ?? 'N/A'); ?>

                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Empleados Inactivos en el Turno -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-orange-500">
                        <div class="px-4 py-5 sm:p-6">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                    Inactivos en Turno
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold <?php echo e(is_null($shiftStats['inactive']) ? 'text-gray-400 dark:text-gray-500' : 'text-orange-600 dark:text-orange-400'); ?>">
                                    <?php echo e($shiftStats['inactive'] ?? 'N/A'); ?>

                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadisticas de Sesiones -->
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Estadisticas de Produccion</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Total Sesiones de Produccion
                            </dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                                0
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Sesiones Activas
                            </dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                                0
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Total Descansos
                            </dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                                <?php echo e($shift->BreakTimes->count()); ?>

                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Descansos Activos
                            </dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                                <?php echo e($shift->BreakTimes->where('active', true)->count()); ?>

                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Empleados del Turno -->
        <div class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Empleados en este turno</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('admin.users.create')): ?>
                    <a href="<?php echo e(route('admin.users.create', ['shift_id' => $shift->id])); ?>"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Agregar Empleado
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shift->allEmployees->count() > 0): ?>
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        No. Empleado
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Nombre
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Posicion
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Area
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $shift->allEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo e(!$employee->active ? 'bg-gray-50 dark:bg-gray-800/50' : ''); ?>">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo e($employee->employee_number ?? 'N/A'); ?>

                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                                            <?php echo e($employee->initials ?? strtoupper(substr($employee->name, 0, 1) . substr($employee->last_name ?? '', 0, 1))); ?>

                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        <?php echo e($employee->full_name ?? $employee->name . ' ' . ($employee->last_name ?? '')); ?>

                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        <?php echo e($employee->email); ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo e($employee->position ?? 'Sin posicion'); ?>

                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo e($employee->area_name ?? ($employee->area->name ?? 'Sin area')); ?>

                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee->active): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    Activo
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    Inactivo
                                                </span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('admin.users.show')): ?>
                                                    <a href="<?php echo e(route('admin.users.show', $employee)); ?>"
                                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                        Ver
                                                    </a>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('admin.users.edit')): ?>
                                                    <a href="<?php echo e(route('admin.users.edit', $employee)); ?>"
                                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                        Editar
                                                    </a>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay empleados</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            No hay empleados asignados a este turno.
                        </p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('admin.users.create')): ?>
                            <div class="mt-6">
                                <a href="<?php echo e(route('admin.users.create', ['shift_id' => $shift->id])); ?>"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    Agregar Empleado
                                </a>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Tabla de Break Times -->
        <div class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Descansos en este turno</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('break-times.create')): ?>
                    <a href="<?php echo e(route('break-times.create', ['shift_id' => $shift->id])); ?>"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuevo Descanso
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shift->BreakTimes->count() > 0): ?>
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Nombre
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Horario
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Duracion
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $shift->BreakTimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $breakTime): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo e($breakTime->name); ?>

                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <?php echo e(\Carbon\Carbon::parse($breakTime->start_break_time)->format('H:i')); ?>

                                                -
                                                <?php echo e(\Carbon\Carbon::parse($breakTime->end_break_time)->format('H:i')); ?>

                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo e($breakTime->formatted_duration); ?>

                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($breakTime->active): ?>
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    Activo
                                                </span>
                                            <?php else: ?>
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    Inactivo
                                                </span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('break-times.show')): ?>
                                                    <a href="<?php echo e(route('break-times.show', $breakTime)); ?>"
                                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                        Ver
                                                    </a>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('break-times.edit')): ?>
                                                    <a href="<?php echo e(route('break-times.edit', $breakTime)); ?>"
                                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                        Editar
                                                    </a>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay descansos
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            No hay descansos asignados a este turno.
                        </p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('break-times.create')): ?>
                            <div class="mt-6">
                                <a href="<?php echo e(route('break-times.create', ['shift_id' => $shift->id])); ?>"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Agregar Descanso
                                </a>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views\livewire\admin\shifts\shift-show.blade.php ENDPATH**/ ?>