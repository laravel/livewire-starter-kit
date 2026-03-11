<div class="space-y-4">
    {{-- Search and Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex-1 max-w-md">
            <flux:input
                wire:model.live.debounce.300ms="searchTerm"
                placeholder="Buscar por WO, PO, cliente o parte..."
                icon="magnifying-glass"
            />
        </div>

        <div class="flex gap-2">
            <select wire:model.live="filterStatus" class="px-3 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                <option value="pending">Pendiente</option>
                <option value="in_progress">En Progreso</option>
                <option value="completed">Completado</option>
            </select>

            @if($searchTerm || $filterStatus)
                <flux:button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Work Orders Table --}}
    @if($workOrders->isEmpty())
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay Work Orders con lotes</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Las Work Orders aparecerán cuando tengan lotes asignados.</p>
        </div>
    @else
        <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg border-2 border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">WO #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PO</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Parte</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lotes</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kits</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($workOrders as $workOrder)
                            @php
                                $po = $workOrder->purchaseOrder;
                                $part = $po->part ?? null;
                                $lotsCount = $workOrder->lots->count();
                                $kitsCount = $workOrder->kits->count();
                                $pendingLots = $workOrder->lots->where('status', 'pending')->count();
                                $completedLots = $workOrder->lots->where('status', 'completed')->count();
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                        {{ $po->wo ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $po->po_number ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $part->number ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $part->description ?? '' }}">
                                    {{ $part->description ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-white">
                                    {{ number_format($po->quantity ?? 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $lotsCount }}
                                    </span>
                                    @if($pendingLots > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 ml-1">
                                            {{ $pendingLots }} pend.
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($part && $part->is_crimp)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            {{ $kitsCount }}
                                        </span>
                                    @else
                                        {{-- No crimp: mostrar indicador de material por lotes --}}
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200" title="No CRIMP — Lote = Kit">
                                            Por Lote
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button 
                                        wire:click="openEditWOStatusModal({{ $workOrder->id }})"
                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium cursor-pointer hover:opacity-80 transition-opacity {{ $workOrder->status ? 'text-white' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}"
                                        style="{{ $workOrder->status ? 'background-color: ' . $workOrder->status->color : '' }}"
                                        title="Clic para cambiar estado"
                                    >
                                        {{ $workOrder->status->name ?? 'Sin estado' }}
                                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </td>
                            </tr>

                            {{-- Expandable Lots Row --}}
                            <tr class="bg-gray-50 dark:bg-gray-900/50">
                                <td colspan="8" class="px-6 py-3">
                                    {{-- Lotes Section --}}
                                    <div class="pl-4 border-l-2 border-blue-300 dark:border-blue-600 mb-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                Lotes de esta WO:
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 ml-1">
                                                    {{ $workOrder->lots->count() }}
                                                </span>
                                            </div>
                                            <button 
                                                wire:click="openCreateLotModal({{ $workOrder->id }})"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Agregar Lote
                                            </button>
                                        </div>
                                        @if($workOrder->lots->isNotEmpty())
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                                @foreach($workOrder->lots as $lot)
                                                    <div class="p-3 bg-white dark:bg-gray-800 rounded border-2 border-gray-200 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
                                                        <div class="flex items-start justify-between mb-2">
                                                            <div class="flex-1">
                                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $lot->lot_number }}</div>
                                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($lot->quantity) }} pcs</div>
                                                                @php
                                                                    $lotStatusColor = match($lot->status) {
                                                                        'pending' => 'zinc',
                                                                        'in_progress' => 'yellow',
                                                                        'completed' => 'green',
                                                                        'cancelled' => 'red',
                                                                        default => 'zinc',
                                                                    };
                                                                @endphp
                                                                <flux:badge :color="$lotStatusColor" size="sm" class="mt-1">
                                                                    {{ ucfirst($lot->status) }}
                                                                </flux:badge>
                                                            </div>
                                                            <div class="flex gap-1">
                                                                <button 
                                                                    wire:click="openEditLotModal({{ $lot->id }})"
                                                                    class="p-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                                    title="Editar lote"
                                                                >
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </button>
                                                                @if($lot->canBeDeleted())
                                                                    <button 
                                                                        wire:click="confirmDeleteLot({{ $lot->id }})"
                                                                        class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                                                        title="Eliminar lote"
                                                                    >
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-500 dark:text-gray-400 italic p-2">
                                                No hay lotes creados para esta WO. Haz clic en "Agregar Lote" para crear uno.
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Kits Section (solo para partes CRIMP) --}}
                                    @if ($part && $part->is_crimp)
                                    <div class="pl-4 border-l-2 border-green-300 dark:border-green-600">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                Kits de esta WO:
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 ml-1">
                                                    {{ $workOrder->kits->count() }}
                                                </span>
                                            </div>
                                            <button 
                                                wire:click="openCreateKitModal({{ $workOrder->id }})"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Agregar Kit
                                            </button>
                                        </div>

                                        @if($workOrder->kits->isEmpty())
                                            <div class="text-xs text-gray-500 dark:text-gray-400 italic p-2">
                                                No hay kits creados para esta WO. Haz clic en "Agregar Kit" para crear uno.
                                            </div>
                                        @else
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                                @foreach($workOrder->kits as $kit)
                                                    <div class="p-3 bg-white dark:bg-gray-800 rounded border-2 border-gray-200 dark:border-gray-700 hover:border-green-400 dark:hover:border-green-500 transition-colors">
                                                        <div class="flex items-start justify-between mb-2">
                                                            <div class="flex-1">
                                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                                    {{ $kit->kit_number }}
                                                                </div>
                                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($kit->quantity ?? 0) }} pz</div>
                                                                @php
                                                                    $kitStatusColor = match($kit->status) {
                                                                        'preparing' => 'yellow',
                                                                        'ready' => 'blue',
                                                                        'released' => 'green',
                                                                        'in_assembly' => 'orange',
                                                                        'rejected' => 'red',
                                                                        default => 'zinc',
                                                                    };
                                                                @endphp
                                                                <flux:badge :color="$kitStatusColor" size="sm" class="mt-1">
                                                                    {{ $kit->status_label }}
                                                                </flux:badge>
                                                            </div>
                                                            <div class="flex gap-1">
                                                                <button 
                                                                    wire:click="openEditKitModal({{ $kit->id }})"
                                                                    class="p-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                                    title="Editar kit"
                                                                >
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </button>
                                                                @if($kit->canBeDeleted())
                                                                    <button 
                                                                        wire:click="confirmDeleteKit({{ $kit->id }})"
                                                                        class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                                                        title="Eliminar kit"
                                                                    >
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                                            @if($kit->preparedBy)
                                                                <div>
                                                                    <span class="font-medium">Preparado:</span> {{ $kit->preparedBy->name }}
                                                                </div>
                                                            @endif
                                                            @if($kit->releasedBy)
                                                                <div>
                                                                    <span class="font-medium">Liberado:</span> {{ $kit->releasedBy->name }}
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <span class="font-medium">Lotes:</span> {{ $kit->lots->count() > 0 ? $kit->lots->pluck('lot_number')->join(', ') : 'Sin lotes' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @else
                                    {{-- No CRIMP: Material section con semaforos por lote --}}
                                    <div class="pl-4 border-l-2 border-amber-300 dark:border-amber-600">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                Material por Lote (No CRIMP — Lote = Kit):
                                            </div>
                                        </div>

                                        @if($workOrder->lots->isEmpty())
                                            <div class="text-xs text-gray-500 dark:text-gray-400 italic p-2">
                                                No hay lotes creados para esta WO.
                                            </div>
                                        @else
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                                @foreach($workOrder->lots as $matLot)
                                                    @php
                                                        $matSt = $matLot->material_status ?? 'pending';
                                                        $matColor = match ($matSt) {
                                                            'released' => 'bg-green-500',
                                                            'rejected' => 'bg-red-500',
                                                            default => 'bg-gray-400',
                                                        };
                                                        $matLabel = match ($matSt) {
                                                            'released' => 'Aprobado',
                                                            'rejected' => 'Rechazado',
                                                            default => 'Pendiente',
                                                        };
                                                        $matBadgeColor = match ($matSt) {
                                                            'released' => 'green',
                                                            'rejected' => 'red',
                                                            default => 'zinc',
                                                        };
                                                    @endphp
                                                    <div class="p-3 bg-white dark:bg-gray-800 rounded border-2 border-gray-200 dark:border-gray-700 hover:border-amber-400 dark:hover:border-amber-500 transition-colors cursor-pointer"
                                                        wire:click="openMaterialModal({{ $matLot->id }})">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-4 h-4 rounded-full {{ $matColor }}"></div>
                                                                <div>
                                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $matLot->lot_number }}</div>
                                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($matLot->quantity) }} pcs</div>
                                                                </div>
                                                            </div>
                                                            <flux:badge :color="$matBadgeColor" size="sm">
                                                                {{ $matLabel }}
                                                            </flux:badge>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $workOrders->links() }}
        </div>
    @endif

    {{-- Create Lot Modal --}}
    @if($showCreateLotModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeCreateLotModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-2 border-gray-200 dark:border-gray-700">
                <form wire:submit="createLot">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Agregar Lote</h3>
                        @if($this->selectedWorkOrder)
                        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-700 rounded-lg">
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                <span class="font-medium">WO:</span> {{ $this->selectedWorkOrder->purchaseOrder->wo ?? 'N/A' }} |
                                <span class="font-medium">Cant. WO:</span> {{ number_format($this->selectedWorkOrder->original_quantity) }}
                            </p>
                        </div>
                        @endif
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número de Lote</label>
                                <input type="text" wire:model="newLotNumber" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: 001">
                                @error('newLotNumber') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad</label>
                                <input type="number" wire:model="newLotQuantity" min="1" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Cantidad de piezas">
                                @error('newLotQuantity') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t-2 border-gray-200 dark:border-gray-700">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Crear Lote</button>
                        <button type="button" wire:click="closeCreateLotModal" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Edit Lot Modal --}}
    @if($showEditLotModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeEditLotModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-2 border-gray-200 dark:border-gray-700">
                <form wire:submit="updateLot">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Editar Lote</h3>
                        @if($selectedLotId && $this->selectedLot)
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número de Lote</label>
                                <input type="text" disabled value="{{ $this->selectedLot->lot_number }}" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad</label>
                                <input type="number" wire:model="lotQuantity" min="1" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('lotQuantity') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descripción</label>
                                <input type="text" wire:model="lotDescription" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                                <select wire:model="lotStatus" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="pending">Pendiente</option>
                                    <option value="in_progress">En Progreso</option>
                                    <option value="completed">Completado</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Comentarios</label>
                                <textarea wire:model="lotComments" rows="3" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t-2 border-gray-200 dark:border-gray-700">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Guardar</button>
                        <button type="button" wire:click="closeEditLotModal" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Lot Confirmation Modal --}}
    @if($showDeleteLotConfirm)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDeleteLot"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-2 border-gray-200 dark:border-gray-700">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 border-2 border-red-200 dark:border-red-700 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Eliminar Lote</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    ¿Estás seguro de que deseas eliminar este lote? Esta acción no se puede deshacer.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t-2 border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="deleteLot" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Eliminar</button>
                    <button type="button" wire:click="cancelDeleteLot" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Create Kit Modal --}}
    @if($showCreateKitModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeCreateKitModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border-2 border-gray-200 dark:border-gray-700">
                <form wire:submit="createKit">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Crear Nuevo Kit</h3>
                        @if($selectedWorkOrderId)
                        @php
                            $createKitWO = \App\Models\WorkOrder::with('purchaseOrder.part')->find($selectedWorkOrderId);
                            $createKitPart = $createKitWO?->purchaseOrder?->part;
                        @endphp
                        @if($createKitPart)
                        <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-lg">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block text-xs">No. Parte</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $createKitPart->number }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block text-xs">Descripción</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $createKitPart->description ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número de Kit</label>
                                    <input type="text" disabled value="(Se generará automáticamente)" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad *</label>
                                    <input type="number" wire:model="kitQuantity" min="1" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: 500">
                                    @error('kitQuantity') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado Inicial</label>
                                <select wire:model="kitStatus" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="preparing">En Preparación</option>
                                    <option value="ready">Listo</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Lotes a Incluir <span class="text-xs text-red-500 font-normal">* (obligatorio)</span></label>
                                <div class="space-y-2 max-h-48 overflow-y-auto border-2 border-gray-200 dark:border-gray-600 rounded-md p-3">
                                    @forelse($this->availableLotsForKit as $lot)
                                        <label class="flex items-center space-x-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer">
                                            <input type="checkbox" wire:model="selectedLots" value="{{ $lot->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-900 dark:text-white">{{ $lot->lot_number }} - {{ number_format($lot->quantity) }} pcs ({{ ucfirst($lot->status) }})</span>
                                        </label>
                                    @empty
                                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No hay lotes disponibles.</p>
                                    @endforelse
                                </div>
                                @error('selectedLots') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notas de Validación</label>
                                <textarea wire:model="kitValidationNotes" rows="3" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t-2 border-gray-200 dark:border-gray-700">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Crear Kit</button>
                        <button type="button" wire:click="closeCreateKitModal" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Edit Kit Modal --}}
    @if($showEditKitModal && $selectedKitId && $this->selectedKit)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeEditKitModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border-2 border-gray-200 dark:border-gray-700">
                <form wire:submit="updateKit">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Editar Kit</h3>
                        @php
                            $editKitPart = $this->selectedKit->workOrder?->purchaseOrder?->part;
                        @endphp
                        @if($editKitPart)
                        <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 rounded-lg">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block text-xs">No. Parte</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $editKitPart->number }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block text-xs">Descripción</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $editKitPart->description ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número de Kit</label>
                                    <input type="text" disabled value="{{ $this->selectedKit->kit_number }}" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad *</label>
                                    <input type="number" wire:model="editKitQuantity" min="1" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: 500">
                                    @error('editKitQuantity') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                                <select wire:model.live="editKitStatus" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="preparing">En Preparación</option>
                                    <option value="ready">Listo</option>
                                    <option value="released">Liberado</option>
                                    <option value="in_assembly">En Ensamble</option>
                                    <option value="rejected">Rechazado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Lotes Asociados</label>
                                <div class="space-y-2 border-2 border-gray-200 dark:border-gray-600 rounded-md p-3 bg-gray-50 dark:bg-gray-900">
                                    @foreach($this->selectedKit->lots as $lot)
                                        <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded">
                                            <span class="text-sm text-gray-900 dark:text-white">{{ $lot->lot_number }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($lot->quantity) }} pcs</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notas de Validación</label>
                                <textarea wire:model="editKitValidationNotes" rows="3" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t-2 border-gray-200 dark:border-gray-700">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Guardar</button>
                        <button type="button" wire:click="closeEditKitModal" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Kit Confirmation Modal --}}
    @if($showDeleteKitConfirm && $selectedKitId && $this->selectedKit)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDeleteKit"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-2 border-gray-200 dark:border-gray-700">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 border-2 border-red-200 dark:border-red-700 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Confirmar Eliminación</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">¿Estás seguro de que deseas eliminar el kit <strong>{{ $this->selectedKit->kit_number }}</strong>?</p>
                                <p class="text-sm text-red-600 mt-2">Esta acción no se puede deshacer.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t-2 border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="deleteKit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Eliminar</button>
                    <button type="button" wire:click="cancelDeleteKit" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Edit WO Status Modal --}}
    @if($showEditWOStatusModal && $selectedWorkOrderId && $this->selectedWorkOrder)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeEditWOStatusModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-2 border-gray-200 dark:border-gray-700">
                <form wire:submit="updateWOStatus">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Cambiar Estado del Work Order</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Work Order</label>
                                <input type="text" disabled value="{{ $this->selectedWorkOrder->purchaseOrder->wo ?? 'N/A' }}" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Parte</label>
                                <input type="text" disabled value="{{ $this->selectedWorkOrder->purchaseOrder->part->number ?? 'N/A' }} - {{ $this->selectedWorkOrder->purchaseOrder->part->description ?? '' }}" class="w-full px-4 py-2.5 text-sm rounded-md border-2 border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado Actual</label>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium text-white" style="background-color: {{ $this->selectedWorkOrder->status->color ?? '#6b7280' }}">
                                        {{ $this->selectedWorkOrder->status->name ?? 'Sin estado' }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nuevo Estado</label>
                                <div class="mt-2 flex gap-3">
                                    <button 
                                        type="button"
                                        wire:click="$set('woStatusAction', 'approved')"
                                        class="flex-1 inline-flex justify-center items-center px-4 py-3 rounded-md text-sm font-medium transition-colors {{ ($woStatusAction ?? '') === 'approved' ? 'bg-green-600 text-white ring-2 ring-green-600 ring-offset-2' : 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200' }}"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Aprobado
                                    </button>
                                    <button 
                                        type="button"
                                        wire:click="$set('woStatusAction', 'rejected')"
                                        class="flex-1 inline-flex justify-center items-center px-4 py-3 rounded-md text-sm font-medium transition-colors {{ ($woStatusAction ?? '') === 'rejected' ? 'bg-red-600 text-white ring-2 ring-red-600 ring-offset-2' : 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200' }}"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Rechazado
                                    </button>
                                </div>
                                @error('woStatusAction') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t-2 border-gray-200 dark:border-gray-700">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Guardar</button>
                        <button type="button" wire:click="closeEditWOStatusModal" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal de Material (No CRIMP: Lote = Kit) --}}
    @if ($showMaterialModal && $selectedLotForMaterial)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="material-modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeMaterialModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-2 border-amber-200 dark:border-amber-700">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4 border-b-2 border-amber-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-white" id="material-modal-title">
                                    Material del Lote
                                </h3>
                                <p class="text-sm text-amber-100 mt-1">
                                    WO: {{ $selectedLotForMaterial->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLotForMaterial->lot_number }}
                                </p>
                            </div>
                            <button wire:click="closeMaterialModal" class="text-white hover:text-amber-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-6">
                        <div class="bg-gray-50 dark:bg-gray-700/50 border-2 border-gray-200 dark:border-gray-600 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Información del Lote</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Parte:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                        {{ $selectedLotForMaterial->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Cantidad:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                        {{ number_format($selectedLotForMaterial->quantity) }} piezas
                                    </span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-gray-500 dark:text-gray-400">Lote:</span>
                                    <span class="ml-2 text-blue-600 dark:text-blue-400 font-medium">
                                        {{ $selectedLotForMaterial->lot_number }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-700 p-3 rounded-lg">
                            <div class="flex items-center text-sm text-amber-700 dark:text-amber-300">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <span>Esta parte <strong>no es CRIMP</strong> — el lote funciona como kit. Apruebe o rechace el material directamente.</span>
                            </div>
                        </div>

                        <div x-data="{ matSt: $wire.entangle('materialStatus') }">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Status del Material
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button"
                                    x-on:click="matSt = 'released'"
                                    :class="matSt === 'released'
                                        ? 'border-green-500 bg-green-50 dark:bg-green-900/30 ring-2 ring-green-300 dark:ring-green-700 shadow-sm'
                                        : 'border-gray-200 dark:border-gray-600 hover:border-green-300 dark:hover:border-green-600 hover:bg-green-50/50 dark:hover:bg-green-900/10'"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200 cursor-pointer">
                                    <div
                                        :class="matSt === 'released' ? 'ring-2 ring-green-300 ring-offset-2 dark:ring-offset-gray-800' : ''"
                                        class="w-8 h-8 rounded-full bg-green-500 mb-2 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span :class="matSt === 'released' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'" class="text-sm font-medium">
                                        Aprobado
                                    </span>
                                </button>

                                <button type="button"
                                    x-on:click="matSt = 'rejected'"
                                    :class="matSt === 'rejected'
                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/30 ring-2 ring-red-300 dark:ring-red-700 shadow-sm'
                                        : 'border-gray-200 dark:border-gray-600 hover:border-red-300 dark:hover:border-red-600 hover:bg-red-50/50 dark:hover:bg-red-900/10'"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200 cursor-pointer">
                                    <div
                                        :class="matSt === 'rejected' ? 'ring-2 ring-red-300 ring-offset-2 dark:ring-offset-gray-800' : ''"
                                        class="w-8 h-8 rounded-full bg-red-500 mb-2 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <span :class="matSt === 'rejected' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300'" class="text-sm font-medium">
                                        Rechazado
                                    </span>
                                </button>
                            </div>

                            <div class="mt-3 text-sm text-center py-2 px-3 rounded-md transition-all duration-200"
                                :class="{
                                    'bg-gray-50 dark:bg-gray-700/20 text-gray-500 dark:text-gray-400': matSt === 'pending',
                                    'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300': matSt === 'released',
                                    'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300': matSt === 'rejected'
                                }">
                                <span x-show="matSt === 'pending'">Material pendiente de revision</span>
                                <span x-show="matSt === 'released'">Material aprobado - Listo para produccion</span>
                                <span x-show="matSt === 'rejected'">Material rechazado - Requiere correccion</span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2 border-t-2 border-gray-200 dark:border-gray-700">
                        <button wire:click="saveMaterialStatus" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Guardar Cambios</button>
                        <button wire:click="closeMaterialModal" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
