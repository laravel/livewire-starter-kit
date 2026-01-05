<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-16 w-16">
                        <div class="h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                            <span class="text-2xl font-medium text-blue-600 dark:text-blue-300">{{ $employee->initials }}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $employee->full_name }}</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $employee->position ?? 'Sin posición' }} · {{ $employee->employee_number ?? 'Sin número' }}
                        </p>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="{{ route('admin.employees.edit', $employee) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    <a href="{{ route('admin.employees.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            @if($employee->active)
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    Empleado Activo
                </span>
            @else
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                    Empleado Inactivo
                </span>
            @endif
        </div>

        <!-- Information Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Personal Information -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Información Personal</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre Completo</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Correo Electrónico</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Empleado</dt>
                            <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-white">{{ $employee->employee_number ?? 'No asignado' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Nacimiento</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $employee->birth_date ? $employee->birth_date->format('d/m/Y') : 'No registrada' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Work Information -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Información Laboral</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Área</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if($employee->area)
                                    <a href="{{ route('admin.areas.show', $employee->area) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                        {{ $employee->area->name }}
                                    </a>
                                @else
                                    Sin área asignada
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Turno</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if($employee->shift)
                                    <a href="{{ route('admin.shifts.show', $employee->shift) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                        {{ $employee->shift->name }}
                                    </a>
                                @else
                                    Sin turno asignado
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Posición / Cargo</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->position ?? 'No especificada' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Ingreso</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $employee->entry_date ? $employee->entry_date->format('d/m/Y') : 'No registrada' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Comments -->
        @if($employee->comments)
            <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Comentarios</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-900 dark:text-white">{{ $employee->comments }}</p>
                </div>
            </div>
        @endif

        <!-- Metadata -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Información del Sistema</h3>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Creación</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Última Actualización</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
