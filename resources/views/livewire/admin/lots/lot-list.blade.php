<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                @if($workOrder)
                    Lotes de {{ $workOrder->purchaseOrder->wo ?? 'N/A' }}
                @else
                    Lotes
                @endif
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($workOrder)
                    {{ $workOrder->purchaseOrder->part->number }} - {{ $workOrder->purchaseOrder->part->description }}
                @else
                    Gestión de lotes de producción
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($workOrder)
                <a href="{{ route('admin.work-orders.show', $workOrder) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver a WO
                </a>
            @endif
            <a href="{{ route('admin.lots.create', ['workOrderId' => $workOrderId]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nuevo Lote
            </a>
        </div>
    </div>

    @if($workOrder)
    <!-- Work Order Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cantidad Original</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($workOrder->original_quantity) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 border-2 border-green-200 dark:border-green-700 rounded-lg p-4">
            <p class="text-xs text-green-600 dark:text-green-400 mb-1">Piezas Enviadas</p>
            <p class="text-2xl font-semibold text-green-700 dark:text-green-300">{{ number_format($workOrder->sent_pieces) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 border-2 border-orange-200 dark:border-orange-700 rounded-lg p-4">
            <p class="text-xs text-orange-600 dark:text-orange-400 mb-1">Pendiente</p>
            <p class="text-2xl font-semibold text-orange-700 dark:text-orange-300">{{ number_format($workOrder->pending_quantity) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 border-2 border-blue-200 dark:border-blue-700 rounded-lg p-4">
            <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Progreso</p>
            @php
                $progress = $workOrder->original_quantity > 0 
                    ? round(($workOrder->sent_pieces / $workOrder->original_quantity) * 100, 1) 
                    : 0;
            @endphp
            <p class="text-2xl font-semibold text-blue-700 dark:text-blue-300">{{ $progress }}%</p>
        </div>
    </div>
    @endif

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <div class="flex flex-col sm:flex-row gap-4 mb-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por número de lote o WO..."
                    class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="sm:w-48">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                <select wire:model.live="filterStatus"
                    class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-40">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Por página</label>
                <select wire:model.live="perPage"
                    class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        @if (session('message'))
            <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-md mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-md mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('lot_number')" class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                # Lote
                                @if ($sortField === 'lot_number')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        @if(!$workOrder)
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Work Order</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('quantity')" class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                Cantidad
                                @if ($sortField === 'quantity')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('status')" class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                Estado
                                @if ($sortField === 'status')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Kit</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($lots as $lot)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $lot->lot_number }}
                                </div>
                                @if($lot->description)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ Str::limit($lot->description, 30) }}
                                    </div>
                                @endif
                            </td>
                            @if(!$workOrder)
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $lot->workOrder->purchaseOrder->wo ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $lot->workOrder->purchaseOrder->part->number }}
                                </div>
                            </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($lot->quantity) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 border-2 border-yellow-200 dark:border-yellow-700',
                                        'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-2 border-blue-200 dark:border-blue-700',
                                        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border-2 border-green-200 dark:border-green-700',
                                        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-2 border-red-200 dark:border-red-700',
                                    ];
                                @endphp
                                <button wire:click="openStatusModal({{ $lot->id }})" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$lot->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border-2 border-gray-200 dark:border-gray-600' }} hover:opacity-80 cursor-pointer transition-opacity">
                                    {{ $lot->status_label }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($lot->kits->count() > 0)
                                    @php $latestKit = $lot->kits->sortByDesc('created_at')->first(); @endphp
                                    <a href="{{ route('admin.kits.show', $latestKit) }}" class="text-sm text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                        {{ $latestKit->kit_number }}
                                    </a>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $latestKit->status_label }}</div>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">Sin kit</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.lots.show', $lot) }}" class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 border-2 border-transparent hover:border-indigo-300 dark:hover:border-indigo-700 rounded-md transition-colors" title="Ver">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.lots.edit', $lot) }}" class="inline-flex items-center justify-center w-8 h-8 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 border-2 border-transparent hover:border-blue-300 dark:hover:border-blue-700 rounded-md transition-colors" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button wire:click="openStatusModal({{ $lot->id }})" class="inline-flex items-center justify-center w-8 h-8 text-teal-600 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/20 border-2 border-transparent hover:border-teal-300 dark:hover:border-teal-700 rounded-md transition-colors" title="Cambiar Estado">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>
                                    @if($lot->canBeDeleted() || $lot->status === \App\Models\Lot::STATUS_COMPLETED)
                                        <button wire:click="confirmDeletion({{ $lot->id }})" class="inline-flex items-center justify-center w-8 h-8 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 border-2 border-transparent hover:border-red-300 dark:hover:border-red-700 rounded-md transition-colors" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $workOrder ? 5 : 6 }}" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="mt-4 text-base font-medium text-gray-900 dark:text-white">No se encontraron lotes</p>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Los lotes aparecerán aquí cuando se creen</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lots->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                {{ $lots->links() }}
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    @if($confirmingDeletion)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-900/50"></div>
                </div>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-2 border-gray-200 dark:border-gray-700">
                    <div class="px-6 pt-6 pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 border-2 border-red-200 dark:border-red-700 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">Confirmar eliminación</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">¿Está seguro de eliminar este lote?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 sm:flex sm:flex-row-reverse">
                        <button wire:click="delete" class="w-full inline-flex justify-center items-center gap-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Eliminar
                        </button>
                        <button wire:click="$set('confirmingDeletion', false)" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                            Seleccione el nuevo estado para el lote
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
