<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.employees.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $employee->full_name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $employee->position ?? 'Sin posición' }} · {{ $employee->employee_number ?? 'Sin número' }}
                </p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.employees.edit', $employee) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            <a href="{{ route('admin.employees.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                Volver
            </a>
        </div>
    </div>

    <!-- Status Badge -->
    <div>
        @if($employee->active)
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                Empleado Activo
            </span>
        @else
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                Empleado Inactivo
            </span>
        @endif
    </div>

    <!-- Information Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Información Personal -->
        <div class="bg-white dark:bg-gray-800 rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Información Personal</h3>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nombre Completo</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Correo Electrónico</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Número de Empleado</dt>
                    <dd class="text-sm font-mono text-gray-900 dark:text-white">{{ $employee->employee_number ?? 'No asignado' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de Nacimiento</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        {{ $employee->birth_date ? $employee->birth_date->format('d/m/Y') : 'No registrada' }}
                    </dd>
                </div>
            </div>
        </div>

        <!-- Información Laboral -->
        <div class="bg-white dark:bg-gray-800 rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Información Laboral</h3>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Área</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        @if($employee->area)
                            <a href="{{ route('admin.areas.show', $employee->area) }}" class="text-blue-900 dark:text-blue-400 hover:underline">
                                {{ $employee->area->name }}
                            </a>
                        @else
                            <span class="text-gray-400">Sin área asignada</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Turno</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        @if($employee->shift)
                            <a href="{{ route('admin.shifts.show', $employee->shift) }}" class="text-blue-900 dark:text-blue-400 hover:underline">
                                {{ $employee->shift->name }}
                            </a>
                        @else
                            <span class="text-gray-400">Sin turno asignado</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Posición / Cargo</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->position ?? 'No especificada' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de Ingreso</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        {{ $employee->entry_date ? $employee->entry_date->format('d/m/Y') : 'No registrada' }}
                    </dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments -->
    @if($employee->comments)
        <div class="bg-white dark:bg-gray-800 rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Comentarios</h3>
            </div>
            <div class="p-4">
                <p class="text-sm text-gray-900 dark:text-white">{{ $employee->comments }}</p>
            </div>
        </div>
    @endif

    <!-- Metadata -->
    <div class="bg-white dark:bg-gray-800 rounded-lg">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Información del Sistema</h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de Creación</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Última Actualización</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $employee->updated_at->format('d/m/Y H:i') }}</dd>
                </div>
            </div>
        </div>
    </div>
</div>
