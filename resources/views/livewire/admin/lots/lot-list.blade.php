<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        @if($workOrder)
                            Lotes de {{ $workOrder->wo_number }}
                        @else
                            Lotes
                        @endif
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        @if($workOrder)
                            {{ $workOrder->purchaseOrder->part->number }} - {{ $workOrder->purchaseOrder->part->description }}
                        @else
                            Gestión de lotes de producción
                        @endif
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    @if($workOrder)
                        <a href="{{ route('admin.work-orders.show', $workOrder) }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver a WO
                        </a>
                    @endif
                    <a href="{{ route('admin.lots.create', ['workOrderId' => $workOrderId]) }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuevo Lote
                    </a>
                </div>
            </div>
        </div>

        @if($workOrder)
        <!-- Work Order Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cantidad Original</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($workOrder->original_quantity) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Piezas Enviadas</p>
                <p class="text-2xl font-semibold text-green-600 dark:text-green-400">{{ number_format($workOrder->sent_pieces) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pendiente</p>
                <p class="text-2xl font-semibold text-orange-600 dark:text-orange-400">{{ number_format($workOrder->pending_quantity) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Progreso</p>
                @php
                    $progress = $workOrder->original_quantity > 0 
                        ? round(($workOrder->sent_pieces / $workOrder->original_quantity) * 100, 1) 
                        : 0;
                @endphp
                <p class="text-2xl font-semibold text-blue-600 dark:text-blue-400">{{ $progress }}%</p>
            </div>
        </div>
        @endif

        <!-- Search and Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <div class="flex-1">
                        <input wire:model.live.debounce.300ms="search" type="text"
                            placeholder="Buscar por número de lote o WO..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <select wire:model.live="filterStatus"
                            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Todos los estados</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select wire:model.live="perPage"
                            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="10">10 por página</option>
                            <option value="25">25 por página</option>
                            <option value="50">50 por página</option>
                        </select>
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

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('lot_number')">
                                    # Lote
                                    @if ($sortField === 'lot_number')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                @if(!$workOrder)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Work Order
                                </th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('quantity')">
                                    Cantidad
                                    @if ($sortField === 'quantity')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                                    Estado
                                    @if ($sortField === 'status')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            @forelse($lots as $lot)
                                <tr>
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
                                        <a href="{{ route('admin.work-orders.show', $lot->workOrder) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                            {{ $lot->workOrder->wo_number }}
                                        </a>
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
                                                'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$lot->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $lot->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.lots.show', $lot) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                                Ver
                                            </a>
                                            @if($lot->canBeStarted())
                                                <button wire:click="startLot({{ $lot->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                                    Iniciar
                                                </button>
                                            @endif
                                            @if($lot->canBeCompleted())
                                                <button wire:click="completeLot({{ $lot->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400">
                                                    Completar
                                                </button>
                                            @endif
                                            @if($lot->canBeDeleted())
                                                <button wire:click="confirmDeletion({{ $lot->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                    Eliminar
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $workOrder ? 4 : 5 }}" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No se encontraron lotes.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $lots->links() }}
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        @if($confirmingDeletion)
            <div class="fixed z-10 inset-0 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Confirmar eliminación</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">¿Está seguro de eliminar este lote?</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse dark:bg-gray-700">
                            <button wire:click="delete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Eliminar
                            </button>
                            <button wire:click="$set('confirmingDeletion', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-600">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
