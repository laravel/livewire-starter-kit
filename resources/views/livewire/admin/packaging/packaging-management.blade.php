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
                placeholder="Buscar por lote, WO, parte o comentario..."
                icon="magnifying-glass"
            />
        </div>

        <div class="flex flex-wrap gap-2 items-center">
            {{-- WO Filter --}}
            <select wire:model.live="filterWorkOrderId" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Todas las WO</option>
                @foreach($workOrdersForFilter as $wo)
                    <option value="{{ $wo->id }}">{{ $wo->purchaseOrder->wo ?? 'N/A' }} — {{ $wo->purchaseOrder->part->number ?? '' }}</option>
                @endforeach
            </select>

            {{-- Lot Filter --}}
            <select wire:model.live="filterLotId" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Todos los lotes</option>
                @foreach($lotsForFilter as $lot)
                    <option value="{{ $lot->id }}">{{ $lot->lot_number }} — {{ $lot->workOrder->purchaseOrder->part->number ?? '' }}</option>
                @endforeach
            </select>

            @if($searchTerm || $filterLotId || $filterWorkOrderId)
                <flux:button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar
                </flux:button>
            @endif

            {{-- Create Button --}}
            @if($lotsForCreate->isNotEmpty())
                <flux:dropdown position="bottom" align="end">
                    <flux:button icon="plus" variant="primary">Nuevo Registro</flux:button>

                    <flux:menu class="w-80 max-h-64 overflow-y-auto">
                        @foreach($lotsForCreate as $lot)
                            <flux:menu.item wire:click="openCreateForLot({{ $lot->id }})">
                                <span class="font-medium">Lote {{ $lot->lot_number }}</span>
                                <span class="text-xs text-gray-500 ml-1">{{ $lot->workOrder->purchaseOrder->wo ?? 'N/A' }} — {{ $lot->workOrder->purchaseOrder->part->number ?? '' }}</span>
                            </flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            @endif
        </div>
    </div>

    {{-- Records Table --}}
    @if($records->isEmpty())
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay registros de empaque</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($searchTerm || $filterLotId || $filterWorkOrderId)
                    No se encontraron registros con los filtros aplicados.
                @else
                    Los registros de empaque se crean desde la lista de envío o aquí.
                @endif
            </p>
        </div>
    @else
        <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lote</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">WO</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Parte</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Disponibles</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-green-600 dark:text-green-400 uppercase">Empacadas</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-orange-600 dark:text-orange-400 uppercase">Sobrantes</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-purple-600 dark:text-purple-400 uppercase">Ajustado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Empacó</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Comentarios</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($records as $record)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $record->id }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                    {{ $record->lot->lot_number ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $record->lot->workOrder->purchaseOrder->wo ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $record->lot->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">
                                    {{ number_format($record->available_pieces) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-green-600 dark:text-green-400">
                                    {{ number_format($record->packed_pieces) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-orange-600 dark:text-orange-400">
                                    {{ number_format($record->surplus_pieces) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right">
                                    @if($record->adjusted_surplus !== null)
                                        <span class="font-medium text-purple-600 dark:text-purple-400">{{ number_format($record->adjusted_surplus) }}</span>
                                        @if($record->adjustment_reason)
                                            <span class="block text-xs text-gray-400" title="{{ $record->adjustment_reason }}">{{ Str::limit($record->adjustment_reason, 20) }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $record->packedBy->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $record->packed_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $record->comments }}">
                                    {{ $record->comments ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <flux:button wire:click="openEditModal({{ $record->id }})" variant="ghost" size="xs" icon="pencil-square" title="Editar" />
                                        <flux:button wire:click="deleteRecord({{ $record->id }})" wire:confirm="¿Eliminar este registro de empaque?" variant="ghost" size="xs" icon="trash" class="text-red-500 hover:text-red-700" title="Eliminar" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $records->links() }}
            </div>
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-indigo-600">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white">
                                {{ $editingId ? 'Editar Registro de Empaque' : 'Nuevo Registro de Empaque' }}
                            </h3>
                            <button wire:click="closeModal" class="text-white hover:text-indigo-200 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-5 space-y-4">

                        {{-- Lot selector --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lote</label>
                            @if($editingId || $formLotId)
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white">
                                    <span class="font-medium">Lote {{ $modalLotNumber }}</span>
                                    <span class="text-gray-500 dark:text-gray-400 ml-2">WO: {{ $modalWo }} — {{ $modalPartNumber }}</span>
                                    <span class="text-gray-500 dark:text-gray-400 ml-2">({{ number_format($modalAvailable) }} pz disponibles)</span>
                                </div>
                            @else
                                <select wire:model.live="formLotId"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Seleccionar lote...</option>
                                    @foreach($lotsForCreate as $lot)
                                        <option value="{{ $lot->id }}">
                                            Lote {{ $lot->lot_number }} — {{ $lot->workOrder->purchaseOrder->wo ?? 'N/A' }} — {{ $lot->workOrder->purchaseOrder->part->number ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            @error('formLotId')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Packed Pieces --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Empacadas</label>
                            <input type="number" wire:model="formPackedPieces" min="0"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('formPackedPieces')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Surplus Pieces --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Sobrantes</label>
                            <input type="number" wire:model="formSurplusPieces" min="0"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('formSurplusPieces')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Adjusted Surplus --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sobrante Ajustado <span class="text-gray-400 font-normal">(opcional)</span></label>
                            <input type="number" wire:model="formAdjustedSurplus" min="0" placeholder="Dejar vacío si no aplica"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('formAdjustedSurplus')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Adjustment Reason --}}
                        @if($formAdjustedSurplus !== null && $formAdjustedSurplus !== '')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Razón del Ajuste</label>
                                <textarea wire:model="formAdjustmentReason" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Indique la razón del ajuste..."></textarea>
                                @error('formAdjustmentReason')
                                    <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        {{-- Packed At --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y Hora de Empaque</label>
                            <input type="datetime-local" wire:model="formPackedAt"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('formPackedAt')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Comments --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios <span class="text-gray-400 font-normal">(opcional)</span></label>
                            <textarea wire:model="formComments" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Observaciones..."></textarea>
                            @error('formComments')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3">
                        <button wire:click="closeModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button wire:click="save"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors cursor-pointer">
                            {{ $editingId ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
