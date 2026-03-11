<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.tables.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Mesa {{ $table->number }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detalles de la mesa de trabajo</p>
            </div>
        </div>
        <a href="{{ route('admin.tables.edit', $table) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar
        </a>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
        <div class="p-6">
            <!-- Status Badge -->
            <div class="mb-6">
                @if($table->active)
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

            <!-- Information Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
                        Información Básica
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded border-2 border-gray-200 dark:border-gray-600">
                                {{ $table->number }}
                            </dd>
                        </div>

                        @if($table->name)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $table->name }}
                                </dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Área</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    {{ $table->area->name }}
                                </span>
                            </dd>
                        </div>

                        @if($table->productionStatus)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado de Producción</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border-2 border-purple-200 dark:border-purple-600 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                        {{ $table->productionStatus->name }}
                                    </span>
                                </dd>
                            </div>
                        @endif

                        @if($table->employees)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Empleados</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $table->employees }} empleado{{ $table->employees > 1 ? 's' : '' }}
                                </dd>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- System Information -->
                <div class="space-y-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
                        Información del Sistema
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Creación</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $table->created_at->format('d/m/Y H:i') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Última Actualización</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $table->updated_at->format('d/m/Y H:i') }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Information -->
            @if($table->brand || $table->model || $table->s_n || $table->asset_number)
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                        Información del Equipo
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($table->brand)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Marca</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $table->brand }}
                                </dd>
                            </div>
                        @endif

                        @if($table->model)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Modelo</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $table->model }}
                                </dd>
                            </div>
                        @endif

                        @if($table->s_n)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Serie</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded border-2 border-gray-200 dark:border-gray-600">
                                    {{ $table->s_n }}
                                </dd>
                            </div>
                        @endif

                        @if($table->asset_number)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Activo</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded border-2 border-gray-200 dark:border-gray-600">
                                    {{ $table->asset_number }}
                                </dd>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Description -->
            @if($table->description)
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Descripción</h3>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border-2 border-gray-200 dark:border-gray-600">
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $table->description }}</p>
                    </div>
                </div>
            @endif

            <!-- Comments -->
            @if($table->comments)
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Comentarios</h3>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border-2 border-gray-200 dark:border-gray-600">
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $table->comments }}</p>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.tables.index') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver a la Lista
                    </a>
                    
                    <a href="{{ route('admin.tables.edit', $table) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Mesa
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
