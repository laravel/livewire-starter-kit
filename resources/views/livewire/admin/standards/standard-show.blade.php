<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Estándar: Parte {{ $standard->part->number }}</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Detalle completo del estándar
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="{{ route('admin.standards.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200"
                        wire:navigate>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                    <a href="{{ route('admin.standards.edit', $standard) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200"
                        wire:navigate>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                </div>
            </div>
        </div>

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info Card -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información General</h2>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parte</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
                                {{ $standard->part->number }}
                                <span class="text-gray-500 dark:text-gray-400 font-normal">- {{ $standard->part->description }}</span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                            <dd class="mt-1">
                                @if($standard->active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Activo
                                    </span>
                                    @if($is_current)
                                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            Vigente
                                        </span>
                                    @endif
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Inactivo
                                    </span>
                                @endif
                            </dd>
                        </div>

                        @if($standard->area)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Área</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $standard->area->name }}</dd>
                            </div>
                        @endif

                        @if($standard->department)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Departamento</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $standard->department->name }}</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Personas 1</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
                                {{ $standard->persons_1 ?? 'N/A' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Personas 2</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
                                {{ $standard->persons_2 ?? 'N/A' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Personas 3</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
                                {{ $standard->persons_3 ?? 'N/A' }}
                            </dd>
                        </div>

                        @if($standard->effective_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Efectiva</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $standard->effective_date->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if($standard->description)
                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $standard->description }}</dd>
                        </div>
                    @endif

                    <!-- Metadata -->
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Creado</dt>
                                <dd class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $standard->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Última Actualización</dt>
                                <dd class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $standard->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acciones</h2>

                        <div class="space-y-2">
                            <button wire:click="toggleActive" type="button"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-{{ $standard->active ? 'red' : 'green' }}-600 hover:bg-{{ $standard->active ? 'red' : 'green' }}-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                @if($standard->active)
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Desactivar
                                @else
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Activar
                                @endif
                            </button>

                            @if($standard->canBeDeleted())
                                <button wire:click="delete" wire:confirm="¿Está seguro de que desea eliminar este estándar?" type="button"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Eliminar
                                </button>
                            @else
                                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                    <p class="text-xs text-yellow-700 dark:text-yellow-300">
                                        Este estándar no puede eliminarse porque tiene órdenes de compra asociadas.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
