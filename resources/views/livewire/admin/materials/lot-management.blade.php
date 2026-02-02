<div class="space-y-4">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header with Search, Filters and Create Button --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex-1 max-w-md">
            <flux:input
                wire:model.live.debounce.300ms="searchTerm"
                placeholder="Buscar por lote, WO o parte..."
                icon="magnifying-glass"
            />
        </div>

        <div class="flex flex-wrap gap-2 items-center">
            {{-- Status Filter --}}
            <select wire:model.live="filterStatus" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                <option value="pending">Pendiente</option>
                <option value="in_progress">En Progreso</option>
                <option value="completed">Completado</option>
                <option value="cancelled">Cancelado</option>
            </select>

            {{-- Work Order Filter --}}
            <select wire:model.live="filterWorkOrderId" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Todas las WO</option>
                @foreach($workOrders as $wo)
                    <option value="{{ $wo->id }}">{{ $wo->purchaseOrder->wo ?? 'N/A' }}</option>
                @endforeach
            </select>

            @if($searchTerm || $filterStatus || $filterWorkOrderId)
                <flux:button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar
                </flux:button>
            @endif

            {{-- Create Button --}}
            @if($allWorkOrders->isNotEmpty())
                <flux:dropdown position="bottom" align="end">
                    <flux:button icon="plus" variant="primary">Crear Lote</flux:button>

                    <flux:menu class="w-72 max-h-64 overflow-y-auto">
                        @foreach($allWorkOrders as $wo)
                            <flux:menu.item wire:click="openCreateModal({{ $wo->id }})">
                                <span class="font-medium">{{ $wo->purchaseOrder->wo ?? 'N/A' }}</span>
                                <span class="text-xs text-gray-500 ml-1">{{ $wo->purchaseOrder->part->number ?? 'N/A' }}</span>
                            </flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            @endif
        </div>
    </div>

    {{-- Lots Table --}}
    @if($lots->isEmpty())
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay lotes</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($searchTerm || $filterStatus || $filterWorkOrderId)
                    No se encontraron lotes con los filtros aplicados.
                @else
                    Comienza creando un lote para una orden de trabajo.
                @endif
            </p>
        </div>
    @else
        <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lote</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Work Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Parte</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cant. Lote</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cant. Sent List</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Proveedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha Recepción</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($lots as $lot)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ $lot->lot_number }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $lot->workOrder->purchaseOrder->wo ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $lot->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-white">
                                    {{ number_format($lot->quantity) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-white">
                                    @php
                                        $sentListQuantity = $lot->workOrder->purchaseOrder->sentLists->sum('pivot.quantity');
                                    @endphp
                                    @if($sentListQuantity > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ number_format($sentListQuantity) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $lot->supplier_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $lot->receipt_date?->format('d/m/Y') ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <flux:badge :color="$lot->status_color">
                                        {{ $lot->status_label }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex justify-center gap-1">
                                        <flux:button wire:click="openEditModal({{ $lot->id }})" size="sm" variant="ghost" icon="pencil" title="Editar" />
                                        @if($lot->canBeDeleted())
                                            <flux:button
                                                wire:click="deleteLot({{ $lot->id }})"
                                                wire:confirm="¿Está seguro de eliminar este lote?"
                                                size="sm"
                                                variant="danger"
                                                icon="trash"
                                                title="Eliminar"
                                            />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $lots->links() }}
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-2xl">
        <form wire:submit="saveLot" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $lotId ? 'Editar Lote' : 'Crear Lote' }}</flux:heading>
                <flux:subheading>Complete los datos del lote</flux:subheading>
            </div>

            <div class="space-y-4">
                {{-- Basic Information --}}
                <div class="grid grid-cols-2 gap-4">
                    <flux:input 
                        wire:model="form.quantity" 
                        label="Cantidad" 
                        type="number"
                        required
                    />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select wire:model="form.status" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="pending">Pendiente</option>
                            <option value="in_progress">En Progreso</option>
                            <option value="completed">Completado</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>
                </div>

                <flux:textarea 
                    wire:model="form.description" 
                    label="Descripción"
                    rows="2"
                />

                {{-- ISO Traceability Section --}}
                <div class="border-t pt-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Trazabilidad ISO</h3>
                    
                    {{-- Batch Numbers --}}
                    <div class="space-y-2 mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Números de Lote de Materia Prima *
                        </label>
                        @foreach($form['raw_material_batch_numbers'] as $index => $batchNumber)
                            <div class="flex gap-2">
                                <flux:input 
                                    wire:model="form.raw_material_batch_numbers.{{ $index }}" 
                                    placeholder="Número de lote"
                                    class="flex-1"
                                />
                                @if($index > 0)
                                    <flux:button 
                                        wire:click="removeBatchNumber({{ $index }})" 
                                        variant="danger"
                                        size="sm"
                                        icon="x-mark"
                                    />
                                @endif
                            </div>
                        @endforeach
                        <flux:button 
                            wire:click="addBatchNumber" 
                            variant="ghost" 
                            size="sm"
                            icon="plus"
                        >
                            Agregar Lote
                        </flux:button>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <flux:input 
                            wire:model="form.receipt_date" 
                            label="Fecha de Recepción *" 
                            type="date"
                            required
                        />
                        <flux:input 
                            wire:model="form.expiration_date" 
                            label="Fecha de Expiración" 
                            type="date"
                        />
                    </div>
                </div>

                <flux:textarea 
                    wire:model="form.comments" 
                    label="Comentarios"
                    rows="2"
                />
            </div>

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $lotId ? 'Actualizar' : 'Crear' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
