<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Kit: {{ $kit->kit_number }}</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Detalle del kit de producción
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="{{ route('admin.kits.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        @if (session('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('message') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info Card -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información General</h2>
                    
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Kit</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $kit->kit_number }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                            <dd class="mt-1">
                                @php
                                    $statusColors = [
                                        'preparing' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'ready' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'released' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'in_assembly' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    ];
                                @endphp
                                <button wire:click="openStatusModal" class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$kit->status] ?? 'bg-gray-100 text-gray-800' }} hover:opacity-80 cursor-pointer transition-opacity">
                                    {{ $kit->status_label }}
                                </button>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Work Order</dt>
                            <dd class="mt-1 text-sm">
                                <a href="{{ route('admin.work-orders.show', $kit->workOrder) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                    {{ $kit->workOrder->purchaseOrder->wo ?? 'N/A' }}
                                </a>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parte</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $kit->workOrder->purchaseOrder->part->number }}
                                <span class="text-gray-500 dark:text-gray-400">- {{ $kit->workOrder->purchaseOrder->part->description }}</span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Validado</dt>
                            <dd class="mt-1">
                                @if($kit->validated)
                                    <span class="inline-flex items-center text-green-600 dark:text-green-400">
                                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Sí
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-gray-500 dark:text-gray-400">
                                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        No
                                    </span>
                                @endif
                            </dd>
                        </div>
                        
                        @if($kit->preparedBy)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Preparado por</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $kit->preparedBy->name }}</dd>
                        </div>
                        @endif
                        
                        @if($kit->releasedBy)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Liberado por</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $kit->releasedBy->name }}</dd>
                        </div>
                        @endif
                    </dl>

                    @if($kit->validation_notes)
                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notas de Validación</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $kit->validation_notes }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions Card -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acciones</h2>
                        
                        <div class="space-y-3">
                            @if(!$kit->validated && $kit->status === 'preparing')
                                <button wire:click="validateKit"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Validar Kit
                                </button>
                            @endif

                            @if($kit->validated && $kit->status === 'preparing')
                                <button wire:click="invalidateKit"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Remover Validación
                                </button>
                            @endif

                            @if($kit->canBeReady())
                                <button wire:click="markAsReady"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Marcar como Listo
                                </button>
                            @endif

                            @if($kit->canBeReleased())
                                <button wire:click="release"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Liberar Kit
                                </button>
                            @endif

                            @if($kit->canStartAssembly())
                                <button wire:click="startAssembly"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                    </svg>
                                    Iniciar Ensamble
                                </button>
                            @endif

                            <button wire:click="openStatusModal"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Cambiar Estado
                            </button>

                            <button wire:click="openIncidentForm"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                Registrar Incidencia
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Timestamps Card -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información del Sistema</h2>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Creado</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $kit->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Actualizado</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $kit->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Incidents Section -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Incidencias (FCA-44)</h2>
                
                @if($kit->incidents->count() > 0)
                    <div class="space-y-4">
                        @foreach($kit->incidents as $incident)
                            <div class="p-4 rounded-lg border {{ $incident->resolved ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' }}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $incident->resolved ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $incident->incident_type_label }}
                                        </span>
                                        @if($incident->fca_44_reference)
                                            <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                                Ref: {{ $incident->fca_44_reference }}
                                            </span>
                                        @endif
                                    </div>
                                    @if(!$incident->resolved)
                                        <button wire:click="resolveIncident({{ $incident->id }})"
                                            class="text-sm text-green-600 hover:text-green-800 dark:text-green-400">
                                            Resolver
                                        </button>
                                    @else
                                        <span class="text-sm text-green-600 dark:text-green-400">
                                            ✓ Resuelto
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $incident->description }}</p>
                                @if($incident->resolved && $incident->resolvedBy)
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Resuelto por {{ $incident->resolvedBy->name }} el {{ $incident->resolved_at->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay incidencias registradas.</p>
                @endif
            </div>
        </div>

        {{-- Modal para Cambiar Estado del Kit --}}
        @if($showStatusModal)
            <div class="fixed z-50 inset-0 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true" wire:click="closeStatusModal">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 dark:bg-gray-800">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    Cambiar Estado del Kit
                                </h3>
                                <button wire:click="closeStatusModal" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Kit: <span class="font-semibold">{{ $kit->kit_number }}</span>
                            </p>

                            <div class="grid grid-cols-2 gap-3">
                                {{-- En Preparación --}}
                                <button wire:click="setNewStatus('preparing')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'preparing' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}">
                                    <div class="w-8 h-8 rounded-full bg-yellow-400 mb-2"></div>
                                    <span class="text-sm font-medium {{ $newStatus === 'preparing' ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        En Preparación
                                    </span>
                                </button>

                                {{-- Listo --}}
                                <button wire:click="setNewStatus('ready')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'ready' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}">
                                    <div class="w-8 h-8 rounded-full bg-blue-500 mb-2"></div>
                                    <span class="text-sm font-medium {{ $newStatus === 'ready' ? 'text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        Listo
                                    </span>
                                </button>

                                {{-- Liberado (Aprobado) --}}
                                <button wire:click="setNewStatus('released')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'released' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}">
                                    <div class="w-8 h-8 rounded-full bg-green-500 mb-2 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium {{ $newStatus === 'released' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        Liberado
                                    </span>
                                </button>

                                {{-- En Ensamble --}}
                                <button wire:click="setNewStatus('in_assembly')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'in_assembly' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}">
                                    <div class="w-8 h-8 rounded-full bg-orange-500 mb-2 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium {{ $newStatus === 'in_assembly' ? 'text-orange-700 dark:text-orange-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        En Ensamble
                                    </span>
                                </button>

                                {{-- Rechazado --}}
                                <button wire:click="setNewStatus('rejected')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all col-span-2 {{ $newStatus === 'rejected' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}">
                                    <div class="w-8 h-8 rounded-full bg-red-500 mb-2 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium {{ $newStatus === 'rejected' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        Rechazado
                                    </span>
                                </button>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse dark:bg-gray-700">
                            <button wire:click="updateKitStatus" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Guardar Cambios
                            </button>
                            <button wire:click="closeStatusModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-600">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Incident Form Modal -->
        @if($showIncidentForm)
            <div class="fixed z-10 inset-0 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                                Registrar Incidencia (FCA-44)
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tipo de Incidencia <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="incident_type"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Seleccionar tipo</option>
                                        @foreach($incidentTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('incident_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Descripción <span class="text-red-500">*</span>
                                    </label>
                                    <textarea wire:model="incident_description" rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Describa la incidencia..."></textarea>
                                    @error('incident_description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Referencia FCA-44
                                    </label>
                                    <input wire:model="fca_44_reference" type="text"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Ej: FCA-44-2025-001">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse dark:bg-gray-700">
                            <button wire:click="saveIncident"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Registrar Incidencia
                            </button>
                            <button wire:click="closeIncidentForm"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-600">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
