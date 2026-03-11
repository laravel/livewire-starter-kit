<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.shifts.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $shift->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }} · {{ $shift->formatted_total_hours }}
                </p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.shifts.edit', $shift) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            <a href="{{ route('admin.shifts.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                Volver
            </a>
        </div>
    </div>

    <!-- Information Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Información del turno</h3>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nombre</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $shift->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Horario</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }} ({{ $shift->formatted_total_hours }})
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Estado</dt>
                    <dd class="text-sm">
                        @if($shift->active)
                            <span class="px-3 py-1 text-xs font-medium rounded-full border-2 border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">Activo</span>
                        @else
                            <span class="px-3 py-1 text-xs font-medium rounded-full border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">Inactivo</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Descansos / Horas netas</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $shift->formatted_break_time }} descanso · {{ $shift->formatted_net_working_hours }} netas</dd>
                </div>
                @if($shift->comments)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Comentarios</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $shift->comments }}</dd>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Registro</h3>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de creación</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $shift->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Última actualización</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $shift->updated_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Empleados en este turno</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $shiftStats['total'] ?? 'N/A' }} ({{ $shiftStats['active'] ?? 'N/A' }} activos)</dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Descansos en este turno -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Descansos en este turno</h3>
            @if(Route::has('admin.break-times.create'))
                <a href="{{ route('admin.break-times.create', ['shift_id' => $shift->id]) }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700">
                    Nuevo descanso
                </a>
            @endif
        </div>
        @if($shift->BreakTimes->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Nombre</th>
                            <th class="px-6 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Horario</th>
                            <th class="px-6 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Duración</th>
                            <th class="px-6 py-2 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($shift->BreakTimes as $breakTime)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-white">{{ $breakTime->name }}</td>
                                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($breakTime->start_break_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($breakTime->end_break_time)->format('H:i') }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $breakTime->formatted_duration }}</td>
                                <td class="px-6 py-3 text-right">
                                    @if(Route::has('admin.break-times.show'))
                                        <a href="{{ route('admin.break-times.show', $breakTime) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">Ver</a>
                                    @endif
                                    @if(Route::has('admin.break-times.edit'))
                                        <a href="{{ route('admin.break-times.edit', $breakTime) }}" class="ml-3 text-blue-600 dark:text-blue-400 hover:underline text-sm">Editar</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                No hay descansos asignados a este turno.
                @if(Route::has('admin.break-times.create'))
                    <a href="{{ route('admin.break-times.create', ['shift_id' => $shift->id]) }}" class="text-blue-600 dark:text-blue-400 hover:underline ml-1">Agregar descanso</a>
                @endif
            </div>
        @endif
    </div>

    <!-- Empleados en este turno -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Empleados en este turno</h3>
            @if(Route::has('admin.employees.create'))
                <a href="{{ route('admin.employees.create') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700">Agregar empleado</a>
            @endif
        </div>
        @if($shift->allEmployees->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Nombre</th>
                            <th class="px-6 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Área</th>
                            <th class="px-6 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Estado</th>
                            <th class="px-6 py-2 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($shift->allEmployees as $employee)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-white">{{ $employee->full_name ?? $employee->name . ' ' . ($employee->last_name ?? '') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $employee->area_name ?? ($employee->area->name ?? '—') }}</td>
                                <td class="px-6 py-3">
                                    @if($employee->active)
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">Activo</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    @if(Route::has('admin.employees.show'))
                                        <a href="{{ route('admin.employees.show', $employee) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">Ver</a>
                                    @endif
                                    @if(Route::has('admin.employees.edit'))
                                        <a href="{{ route('admin.employees.edit', $employee) }}" class="ml-3 text-blue-600 dark:text-blue-400 hover:underline text-sm">Editar</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">No hay empleados asignados a este turno.</div>
        @endif
    </div>
</div>
