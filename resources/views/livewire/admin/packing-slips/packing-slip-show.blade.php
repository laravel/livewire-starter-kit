<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center space-x-3">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white font-mono">
                            {{ $packingSlip->ps_number }}
                        </h1>
                        <div class="flex items-center mt-2 space-x-3">
                            @php
                                $badgeClasses = match($packingSlip->status) {
                                    'pending'   => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                    'shipped'   => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                    default     => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $badgeClasses }}">
                                {{ $packingSlip->statusLabel }}
                            </span>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Packing Slip</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 sm:mt-0 flex flex-wrap gap-2 items-center">
                    {{-- Selector de estado universal: permite cambiar a cualquier estado libremente --}}
                    <div class="flex items-center gap-2">
                        <select wire:model="selectedStatus"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            @foreach (\App\Models\PackingSlip::STATUSES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button wire:click="updateStatus"
                                class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                            Guardar estado
                        </button>
                    </div>

                    @if (!$packingSlip->isShipped())
                        {{-- Botón toggle para el panel de edición de lotes integrado --}}
                        <button wire:click="toggleEditingLots"
                                class="inline-flex items-center px-4 py-2 {{ $editingLots ? 'bg-amber-600 hover:bg-amber-700' : 'bg-gray-600 hover:bg-gray-700' }} text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ $editingLots ? 'Cancelar edición' : 'Editar lotes' }}
                        </button>
                    @endif

                    <a href="{{ route('admin.packing-slips.index') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver a la lista
                    </a>
                </div>
            </div>
        </div>

        <!-- Info General -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información General</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de PS</p>
                        <p class="text-base font-mono text-gray-900 dark:text-white mt-1">{{ $packingSlip->ps_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha del Documento</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $packingSlip->document_date?->format('d/m/Y') ?? '-' }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Campo DATE del FPL-10</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Creado por</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $packingSlip->creator?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Creación</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $packingSlip->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    @if ($packingSlip->isShipped())
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Despachado por</p>
                            <p class="text-base text-gray-900 dark:text-white mt-1">{{ $packingSlip->shipper?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Despacho</p>
                            <p class="text-base text-gray-900 dark:text-white mt-1">{{ $packingSlip->shipped_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    @endif

                    <div class="{{ $packingSlip->isShipped() ? '' : 'md:col-span-3' }}">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Notas</p>
                        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $packingSlip->notes ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================ -->
        <!-- PANEL DE EDICIÓN DE LOTES (visible solo cuando !shipped y editingLots) -->
        <!-- ================================================================ -->
        @if (!$packingSlip->isShipped() && $editingLots)
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border-2 border-amber-400 dark:border-amber-600 overflow-hidden mb-6"
                 id="lot-editing-panel">
                <div class="px-6 py-4 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-200">
                                Editar Lotes del Packing Slip
                            </h2>
                        </div>
                        <span class="text-sm text-amber-700 dark:text-amber-300">
                            {{ count($selectedLotIds) }} lote(s) seleccionado(s)
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                        Selecciona o deselecciona lotes. Los cambios se aplican al guardar.
                    </p>
                </div>

                <div class="p-6">
                    @error('selectedLotIds')
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg mb-4">
                            {{ $message }}
                        </div>
                    @enderror

                    @if ($availableLots->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-10">
                                            Sel.
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">N° Lote</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Part Number</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">WO</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty Packed</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Label Spec</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                    @foreach ($availableLots as $lot)
                                        @php $isSelected = in_array($lot->id, $selectedLotIds); @endphp
                                        <tr class="{{ $isSelected ? 'bg-blue-50 dark:bg-blue-900/20' : '' }} hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-100">
                                            <td class="px-4 py-3">
                                                <input
                                                    type="checkbox"
                                                    wire:click="toggleLot({{ $lot->id }})"
                                                    {{ $isSelected ? 'checked' : '' }}
                                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                >
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $lot->lot_number }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $lot->workOrder?->purchaseOrder?->part?->number ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">
                                                @php
                                                    $woPreview = $lot->workOrder?->external_wo_number
                                                        ? 'W0' . $lot->workOrder->external_wo_number . str_pad($lot->lot_number, 3, '0', STR_PAD_LEFT)
                                                        : null;
                                                @endphp
                                                @if ($woPreview)
                                                    {{ $woPreview }}
                                                @else
                                                    <span class="text-orange-500 text-xs font-sans">Sin WO externo</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-white">
                                                {{ number_format($lot->quantity_packed_final ?? $lot->quantity ?? 0) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                @if ($isSelected)
                                                    <input
                                                        type="text"
                                                        wire:model="labelSpecs.{{ $lot->id }}"
                                                        maxlength="50"
                                                        placeholder="Spec de etiqueta..."
                                                        class="border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 text-sm w-full max-w-xs focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                                    >
                                                    @error("labelSpecs.{$lot->id}")
                                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                    @enderror
                                                @else
                                                    <span class="text-gray-400 text-sm">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 dark:bg-gray-900 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No hay lotes disponibles para agregar.</p>
                        </div>
                    @endif

                    <!-- Acciones del panel de lotes -->
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button wire:click="toggleEditingLots"
                                class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                            Cancelar
                        </button>
                        <button wire:click="updateLots"
                                class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Guardar cambios de lotes
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabla de Items -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Items del Packing Slip
                    </h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $packingSlip->items->count() }} {{ $packingSlip->items->count() === 1 ? 'item' : 'items' }}
                    </span>
                </div>

                @if ($packingSlip->items->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Work Order</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"># PO</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Label Spec</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                @foreach ($itemsGroupedByPo as $poNumber => $poItems)
                                    @foreach ($poItems as $item)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-white">
                                                {{ $item->wo_number_ps ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                {{ $item->lot?->workOrder?->purchaseOrder?->po_number ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $item->lot?->workOrder?->purchaseOrder?->part?->item_number ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $item->lot?->workOrder?->purchaseOrder?->part?->number ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-white">
                                                {{ number_format($item->quantity_packed) }}
                                            </td>
                                            {{-- Celda editable: Date (editable en todos los estados) --}}
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"
                                                x-data="{ editing: false, value: '{{ $item->lot_date_code ?? '' }}' }">
                                                <span x-show="!editing" @click="editing = true"
                                                      class="cursor-pointer hover:text-blue-600 hover:underline min-w-[80px] inline-block"
                                                      x-text="value || '-'"></span>
                                                <input x-show="editing" x-model="value" type="text"
                                                       maxlength="20"
                                                       placeholder="ej: 250512A22"
                                                       class="border border-blue-400 rounded px-2 py-0.5 text-sm w-36 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                       @blur="editing = false; $wire.updateItemDate({{ $item->id }}, value)"
                                                       @keydown.enter="editing = false; $wire.updateItemDate({{ $item->id }}, value)"
                                                       @keydown.escape="editing = false"
                                                       x-effect="if (editing) $el.focus()">
                                            </td>
                                            {{-- Celda editable: Label Spec (editable en todos los estados) --}}
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"
                                                x-data="{ editing: false, value: '{{ $item->label_spec ?? '' }}' }">
                                                <span x-show="!editing" @click="editing = true"
                                                      class="cursor-pointer hover:text-blue-600 hover:underline min-w-[80px] inline-block"
                                                      x-text="value || '-'"></span>
                                                <input x-show="editing" x-model="value" type="text"
                                                       class="border border-blue-400 rounded px-2 py-0.5 text-sm w-36 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                       @blur="editing = false; $wire.updateItemLabelSpec({{ $item->id }}, value)"
                                                       @keydown.enter="editing = false; $wire.updateItemLabelSpec({{ $item->id }}, value)"
                                                       @keydown.escape="editing = false"
                                                       x-effect="if (editing) $el.focus()"
                                                       placeholder="Label spec...">
                                            </td>
                                        </tr>
                                    @endforeach
                                    {{-- Fila de subtotal por PO: solo si el grupo tiene mas de 1 item --}}
                                    @if ($poItems->count() > 1)
                                        <tr class="bg-blue-50 dark:bg-blue-900/20 border-t-2 border-blue-200 dark:border-blue-700">
                                            <td colspan="3" class="px-4 py-2"></td>
                                            <td class="px-4 py-2 text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wider text-right">
                                                Total PO {{ $poNumber }}:
                                            </td>
                                            <td class="px-4 py-2 text-sm font-bold text-right text-blue-700 dark:text-blue-300">
                                                {{ number_format($poItems->sum('quantity_packed')) }}
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 text-right uppercase tracking-wider">
                                        Total de piezas:
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-right text-gray-900 dark:text-white">
                                        {{ number_format($packingSlip->items->sum('quantity_packed')) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 bg-gray-50 dark:bg-gray-900 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Este Packing Slip no tiene items.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Metadatos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Creado</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $packingSlip->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Última actualización</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $packingSlip->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
