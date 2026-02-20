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
                placeholder="Buscar por kit, WO o parte..."
                icon="magnifying-glass"
            />
        </div>

        <div class="flex flex-wrap gap-2 items-center">
            {{-- Status Filter --}}
            <select wire:model.live="filterStatus" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                <option value="preparing">En Preparación</option>
                <option value="ready">Listo</option>
                <option value="released">Liberado</option>
                <option value="in_assembly">En Ensamble</option>
                <option value="rejected">Rechazado</option>
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

            {{-- Create Button - Always visible but disabled if no lots selected --}}
            @if($availableLots->isNotEmpty())
                <flux:button
                    wire:click="openCreateModal"
                    icon="plus"
                    variant="primary"
                    :disabled="empty($selectedLots)"
                >
                    Crear Kit @if(!empty($selectedLots))({{ count($selectedLots) }})@endif
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Available Lots for Selection --}}
    @if($availableLots->isNotEmpty() && empty($kitId))
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-blue-900 dark:text-blue-200">Lotes Disponibles para Kit</h3>
                @if(!empty($selectedLots))
                    <span class="text-sm text-blue-700 dark:text-blue-300">
                        {{ count($selectedLots) }} lote(s) seleccionado(s)
                    </span>
                @endif
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 max-h-64 overflow-y-auto">
                @foreach($availableLots as $lot)
                    @php
                        $isSelected = in_array($lot->id, $selectedLots);
                    @endphp
                    <div
                        wire:click="toggleLotSelection({{ $lot->id }})"
                        class="flex items-center p-3 bg-white dark:bg-gray-800 border rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors {{ $isSelected ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/40 ring-1 ring-blue-500' : 'border-gray-200 dark:border-gray-700' }}"
                    >
                        <div class="flex-shrink-0 w-5 h-5 border-2 rounded flex items-center justify-center {{ $isSelected ? 'bg-blue-600 border-blue-600' : 'border-gray-300 dark:border-gray-600' }}">
                            @if($isSelected)
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </div>
                        <div class="ml-3 text-sm">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $lot->lot_number }}</div>
                            <div class="text-gray-500 dark:text-gray-400">{{ $lot->workOrder->purchaseOrder->wo ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">{{ number_format($lot->quantity) }} pcs</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Kits Table --}}
    @if($kits->isEmpty())
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay kits</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($searchTerm || $filterStatus || $filterWorkOrderId)
                    No se encontraron kits con los filtros aplicados.
                @else
                    Selecciona lotes arriba y crea un kit para comenzar.
                @endif
            </p>
        </div>
    @else
        <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Work Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Parte</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lotes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Preparado Por</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ciclo</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($kits as $kit)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ $kit->kit_number }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $kit->workOrder->purchaseOrder->wo ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <div>{{ $kit->workOrder->purchaseOrder->part->number ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">{{ $kit->workOrder->purchaseOrder->part->description ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format($kit->quantity ?? 0) }} pz
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $kit->lots->count() }} lotes
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $kit->preparedBy->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <flux:badge :color="$kit->status_color">
                                        {{ $kit->status_label }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    @if($kit->current_approval_cycle > 1)
                                        <span class="text-orange-600 dark:text-orange-400 font-medium">Ciclo {{ $kit->current_approval_cycle }}</span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex justify-center gap-1">
                                        @if($kit->canBeEdited())
                                            <flux:button wire:click="openEditModal({{ $kit->id }})" size="sm" variant="ghost" icon="pencil" title="Editar" />
                                        @endif

                                        @if($kit->status === 'preparing')
                                            <flux:button
                                                wire:click="submitToInspection({{ $kit->id }})"
                                                size="sm"
                                                variant="primary"
                                                icon="paper-airplane"
                                                title="Enviar a Inspección"
                                            />
                                        @endif

                                        @if($kit->approvalCycles->isNotEmpty())
                                            <flux:button
                                                wire:click="showApprovalHistoryModal({{ $kit->id }})"
                                                size="sm"
                                                variant="ghost"
                                                icon="clock"
                                                title="Historial"
                                            />
                                        @endif

                                        @if($kit->canBeDeleted())
                                            <flux:button
                                                wire:click="deleteKit({{ $kit->id }})"
                                                wire:confirm="¿Está seguro de eliminar este kit?"
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
            {{ $kits->links() }}
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-2xl">
        <form wire:submit="saveKit" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $kitId ? 'Editar Kit' : 'Crear Kit' }}</flux:heading>
                <flux:subheading>Complete los datos del kit</flux:subheading>
            </div>

            <div class="space-y-4">
                {{-- Part Info (if lots selected) --}}
                @if(!empty($selectedLots))
                    @php
                        $firstLotInfo = \App\Models\Lot::with('workOrder.purchaseOrder.part')->find($selectedLots[0]);
                        $partInfo = $firstLotInfo?->workOrder?->purchaseOrder?->part;
                    @endphp
                    @if($partInfo)
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block text-xs">No. Parte</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $partInfo->number }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block text-xs">Descripción</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $partInfo->description ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <flux:input 
                        wire:model="form.kit_number" 
                        label="Número de Kit" 
                        required
                        readonly
                    />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad *</label>
                        <input wire:model="form.quantity" type="number" min="1"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ej: 500">
                        @error('form.quantity')
                            <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Selected Lots Display --}}
                @if(!empty($selectedLots))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Lotes Seleccionados ({{ count($selectedLots) }})
                        </label>
                        <div class="space-y-1">
                            @foreach($selectedLots as $lotId)
                                @php
                                    $lot = $availableLots->firstWhere('id', $lotId);
                                @endphp
                                @if($lot)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                        <span class="text-sm">
                                            Lote {{ $lot->lot_number }} - {{ $lot->workOrder->purchaseOrder->wo ?? 'N/A' }} ({{ number_format($lot->quantity) }} pcs)
                                        </span>
                                        @if(!$kitId)
                                            <flux:button 
                                                wire:click="deselectLot({{ $lotId }})" 
                                                size="sm" 
                                                variant="ghost"
                                                icon="x-mark"
                                            />
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <flux:textarea 
                    wire:model="form.validation_notes" 
                    label="Notas de Validación"
                    rows="3"
                />
            </div>

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $kitId ? 'Actualizar' : 'Crear' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Approval History Modal --}}
    <flux:modal wire:model="showApprovalHistory" class="max-w-3xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Historial de Aprobaciones</flux:heading>
                <flux:subheading>{{ $kit->kit_number ?? '' }}</flux:subheading>
            </div>

        @if($kit && $kit->approvalCycles->isNotEmpty())
            <div class="space-y-4">
                @foreach($kit->approvalCycles as $cycle)
                    <div class="border rounded-lg p-4 {{ $cycle->isRejected() ? 'border-red-200 bg-red-50' : ($cycle->isApproved() ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50') }}">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-gray-900">Ciclo #{{ $cycle->cycle_number }}</h4>
                            <flux:badge :color="$cycle->isApproved() ? 'green' : ($cycle->isRejected() ? 'red' : 'yellow')">
                                {{ ucfirst($cycle->status) }}
                            </flux:badge>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Enviado por:</span>
                                <span class="font-medium">{{ $cycle->submitter->name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Fecha:</span>
                                <span class="font-medium">{{ $cycle->submitted_at->format('d/m/Y H:i') }}</span>
                            </div>
                            
                            @if($cycle->reviewed_at)
                                <div>
                                    <span class="text-gray-500">Revisado por:</span>
                                    <span class="font-medium">{{ $cycle->reviewer->name ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Fecha revisión:</span>
                                    <span class="font-medium">{{ $cycle->reviewed_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                        </div>

                        @if($cycle->rejection_reason)
                            <div class="mt-3 p-3 bg-white rounded border border-red-200">
                                <p class="text-sm font-medium text-red-900 mb-1">Razón de Rechazo:</p>
                                <p class="text-sm text-gray-700">{{ $cycle->rejection_reason }}</p>
                            </div>
                        @endif

                        @if($cycle->comments)
                            <div class="mt-2 text-sm text-gray-600">
                                <span class="font-medium">Comentarios:</span> {{ $cycle->comments }}
                            </div>
                        @endif

                        @if($cycle->getDuration())
                            <div class="mt-2 text-xs text-gray-500">
                                Duración: {{ $cycle->getDuration() }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">No hay historial de aprobaciones.</p>
        @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Cerrar</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
