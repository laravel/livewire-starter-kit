<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.machines.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $machine->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detalles de la máquina</p>
            </div>
        </div>
        <a href="{{ route('admin.machines.edit', $machine) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar
        </a>
    </div>

    <!-- Status Badge -->
    <div>
        @if($machine->active)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border-2 border-green-200 dark:border-green-600 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Activa
            </span>
        @else
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border-2 border-red-200 dark:border-red-600 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                Inactiva
            </span>
        @endif
    </div>

    <!-- Main Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                    Información Básica
                </h3>
                <div class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $machine->name }}</dd>
                    </div>
                    
                    @if($machine->brand)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Marca</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $machine->brand }}</dd>
                        </div>
                    @endif
                    
                    @if($machine->model)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Modelo</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $machine->model }}</dd>
                        </div>
                    @endif
                    
                    @if($machine->sn)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Serie</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded border-2 border-gray-200 dark:border-gray-600">
                                {{ $machine->sn }}
                            </dd>
                        </div>
                    @endif
                    
                    @if($machine->asset_number)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Activo</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded border-2 border-gray-200 dark:border-gray-600">
                                {{ $machine->asset_number }}
                            </dd>
                        </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Área</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if($machine->area)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    {{ $machine->area->name }}
                                </span>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">Sin área asignada</span>
                            @endif
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operational Information -->
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                    Información Operacional
                </h3>
                <div class="space-y-3">
                    @if($machine->employees)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Empleados</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border-2 border-purple-200 dark:border-purple-600 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                    {{ $machine->employees }} empleado{{ $machine->employees > 1 ? 's' : '' }}
                                </span>
                            </dd>
                        </div>
                    @endif
                    
                    @if($machine->setup_time)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tiempo de Setup</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border-2 border-yellow-200 dark:border-yellow-600 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300">
                                    {{ $machine->setup_time }} hora{{ $machine->setup_time != 1 ? 's' : '' }}
                                </span>
                            </dd>
                        </div>
                    @endif
                    
                    @if($machine->maintenance_time)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tiempo de Mantenimiento</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border-2 border-orange-200 dark:border-orange-600 bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300">
                                    {{ $machine->maintenance_time }} hora{{ $machine->maintenance_time != 1 ? 's' : '' }}
                                </span>
                            </dd>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    @if($machine->comments)
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                    Comentarios
                </h3>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border-2 border-gray-200 dark:border-gray-600">
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $machine->comments }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- System Information -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
        <div class="p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                Información del Sistema
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Creación</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $machine->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Última Actualización</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $machine->updated_at->format('d/m/Y H:i') }}</dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.machines.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver a la Lista
        </a>
        
        <a href="{{ route('admin.machines.edit', $machine) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar Máquina
        </a>
    </div>
</div>
