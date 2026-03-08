<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Lote: {{ $lot->lot_number }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detalle del lote de producción</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.lots.edit', $lot) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            <a href="{{ route('admin.lots.index', ['workOrderId' => $lot->work_order_id]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>

    @if (session('message'))
        <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info Card -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información General</h2>
                
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Número de Lote</dt>
                        <dd class="text-sm text-gray-900 dark:text-white font-semibold">{{ $lot->lot_number }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Estado</dt>
                        <dd class="mt-1">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 border-2 border-yellow-200 dark:border-yellow-700',
                                    'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-2 border-blue-200 dark:border-blue-700',
                                    'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border-2 border-green-200 dark:border-green-700',
                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-2 border-red-200 dark:border-red-700',
                                ];
                            @endphp
                            <button wire:click="openStatusModal" class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusColors[$lot->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border-2 border-gray-200 dark:border-gray-600' }} hover:opacity-80 cursor-pointer transition-opacity">
                                {{ $lot->status_label }}
                            </button>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Work Order</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $lot->workOrder->purchaseOrder->wo ?? '-' }}
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Parte</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $lot->workOrder->purchaseOrder->part->number }}
                            <span class="text-gray-500 dark:text-gray-400">- {{ $lot->workOrder->purchaseOrder->part->description }}</span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cantidad</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($lot->quantity) }}</dd>
                    </div>
                </dl>

                @if($lot->description)
                    <div class="mt-6 pt-4 border-t-2 border-gray-200 dark:border-gray-700">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Descripción</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $lot->description }}</dd>
                    </div>
                @endif

                @if($lot->comments)
                    <div class="mt-4">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Comentarios</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $lot->comments }}</dd>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions Card -->
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acciones</h2>
                    
                    <div class="space-y-3">
                        @if($lot->canBeStarted())
                            <button wire:click="startLot"
                                class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Iniciar Producción
                            </button>
                        @endif

                        @if($lot->canBeCompleted())
                            <button wire:click="completeLot"
                                class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Completar Lote
                            </button>
                        @endif

                        @if($lot->canBeCancelled())
                            <button wire:click="cancelLot"
                                class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Cancelar Lote
                            </button>
                        @endif

                        @if($lot->status === 'completed')
                            <div class="p-4 bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-700 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm text-green-700 dark:text-green-300">Lote completado</span>
                                </div>
                            </div>
                        @endif

                        @if($lot->status === 'cancelled')
                            <div class="p-4 bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-700 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm text-red-700 dark:text-red-300">Lote cancelado</span>
                                </div>
                            </div>
                        @endif

                        <button wire:click="openStatusModal"
                            class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Cambiar Estado
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kit Asociado Card -->
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Kit Asociado</h2>
                    @if($lot->kits->count() > 0)
                        @foreach($lot->kits as $kit)
                            <div class="p-3 bg-gray-50 dark:bg-gray-900/50 border-2 border-gray-200 dark:border-gray-700 rounded-lg mb-2">
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('admin.kits.show', $kit) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $kit->kit_number }}
                                    </a>
                                    @php
                                        $kitStatusColors = [
                                            'preparing' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 border-2 border-yellow-200 dark:border-yellow-700',
                                            'ready' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-2 border-blue-200 dark:border-blue-700',
                                            'released' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border-2 border-green-200 dark:border-green-700',
                                            'in_assembly' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 border-2 border-orange-200 dark:border-orange-700',
                                            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-2 border-red-200 dark:border-red-700',
                                        ];
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $kitStatusColors[$kit->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border-2 border-gray-200 dark:border-gray-600' }}">
                                        {{ $kit->status_label }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic">Sin kit asociado</p>
                    @endif
                </div>
            </div>

            <!-- Work Order Summary Card -->
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resumen de WO</h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Cantidad Original</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($lot->workOrder->original_quantity) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Piezas Enviadas</dt>
                            <dd class="text-sm font-medium text-green-600 dark:text-green-400">{{ number_format($lot->workOrder->sent_pieces) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Pendiente</dt>
                            <dd class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ number_format($lot->workOrder->pending_quantity) }}</dd>
                        </div>
                        <div class="pt-2 border-t-2 border-gray-200 dark:border-gray-700">
                            @php
                                $progress = $lot->workOrder->original_quantity > 0 
                                    ? round(($lot->workOrder->sent_pieces / $lot->workOrder->original_quantity) * 100, 1) 
                                    : 0;
                            @endphp
                            <div class="flex justify-between mb-1">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Progreso</dt>
                                <dd class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ $progress }}%</dd>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Timestamps Card -->
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información del Sistema</h2>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Creado</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $lot->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Actualizado</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $lot->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para Cambiar Estado del Lote --}}
    @if($showStatusModal)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" wire:click="closeStatusModal">
                    <div class="absolute inset-0 bg-gray-900/50"></div>
                </div>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-2 border-gray-200 dark:border-gray-700">
                    <div class="px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                                Cambiar Estado del Lote
                            </h3>
                            <button wire:click="closeStatusModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Lote: <span class="font-semibold">{{ $lot->lot_number }}</span>
                        </p>

                        <div class="grid grid-cols-2 gap-3">
                            {{-- Pendiente --}}
                            <button wire:click="setNewStatus('pending')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'pending' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                <div class="w-8 h-8 rounded-full bg-yellow-400 border-2 border-yellow-300 dark:border-yellow-600 mb-2"></div>
                                <span class="text-sm font-medium {{ $newStatus === 'pending' ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    Pendiente
                                </span>
                            </button>

                            {{-- En Progreso --}}
                            <button wire:click="setNewStatus('in_progress')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'in_progress' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                <div class="w-8 h-8 rounded-full bg-blue-500 border-2 border-blue-300 dark:border-blue-600 mb-2"></div>
                                <span class="text-sm font-medium {{ $newStatus === 'in_progress' ? 'text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    En Progreso
                                </span>
                            </button>

                            {{-- Completado --}}
                            <button wire:click="setNewStatus('completed')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'completed' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                <div class="w-8 h-8 rounded-full bg-green-500 border-2 border-green-300 dark:border-green-600 mb-2 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium {{ $newStatus === 'completed' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    Completado
                                </span>
                            </button>

                            {{-- Cancelado --}}
                            <button wire:click="setNewStatus('cancelled')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'cancelled' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                <div class="w-8 h-8 rounded-full bg-red-500 border-2 border-red-300 dark:border-red-600 mb-2 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium {{ $newStatus === 'cancelled' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    Cancelado
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 sm:flex sm:flex-row-reverse">
                        <button wire:click="updateLotStatus" class="w-full inline-flex justify-center items-center gap-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Guardar Cambios
                        </button>
                        <button wire:click="closeStatusModal" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
