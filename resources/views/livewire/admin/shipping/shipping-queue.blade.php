<div class="min-h-screen bg-gray-50 dark:bg-gray-900">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cola de Despacho</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Lotes listos para incluir en un Packing Slip (FPL-10)
                    </p>
                </div>
                @if($canCreatePs && !empty($selectedLotIds))
                    <button
                        wire:click="openCreatePsModal"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear Packing Slip ({{ count($selectedLotIds) }})
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Mensajes de estado --}}
        @if($successMessage)
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-green-800 dark:text-green-200">{{ $successMessage }}</p>
            </div>
        @endif

        @if($errorMessage)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-800 dark:text-red-200">{{ $errorMessage }}</p>
            </div>
        @endif

        {{-- Filtros --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                {{-- Busqueda --}}
                <div class="flex-1">
                    <label class="sr-only">Buscar</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            wire:model.live.debounce.300ms="searchTerm"
                            type="text"
                            placeholder="Buscar por lote, parte o WO externo..."
                            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                    </div>
                </div>

                {{-- Filtro por tipo de cierre --}}
                <div class="sm:w-56">
                    <select
                        wire:model.live="filterClosedByType"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500"
                    >
                        @foreach($closureTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Limpiar seleccion --}}
                @if(!empty($selectedLotIds))
                    <button
                        wire:click="clearSelection"
                        class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        Limpiar seleccion ({{ count($selectedLotIds) }})
                    </button>
                @endif
            </div>
        </div>

        {{-- Tabla de lotes en cola --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($lotsInQueue->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No hay lotes en la cola de despacho</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                        Los lotes aparecen aqui cuando Empaque cierra un lote (viajero + decision de cierre)
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                @if($canCreatePs)
                                    <th class="w-10 px-4 py-3 text-left">
                                        <span class="sr-only">Seleccionar</span>
                                    </th>
                                @endif
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Lote</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">WO / Parte</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">WO Externo</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Qty Empacada</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Tipo Cierre</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Fecha Cierre</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach($lotsInQueue as $lot)
                                @php
                                    $hasExternalWo = $lot->workOrder?->hasExternalWoNumber();
                                    $isSelected    = in_array($lot->id, $selectedLotIds);
                                @endphp
                                <tr
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors {{ $isSelected ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}"
                                    @if($canCreatePs) wire:click="toggleLot({{ $lot->id }})" style="cursor: pointer;" @endif
                                >
                                    @if($canCreatePs)
                                        <td class="px-4 py-3" wire:click.stop>
                                            <input
                                                type="checkbox"
                                                wire:click="toggleLot({{ $lot->id }})"
                                                @checked($isSelected)
                                                @disabled(!$hasExternalWo)
                                                class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 disabled:opacity-40"
                                            >
                                        </td>
                                    @endif

                                    {{-- Lote --}}
                                    <td class="px-4 py-3">
                                        <span class="font-mono font-medium text-gray-900 dark:text-white">
                                            {{ $lot->lot_number }}
                                        </span>
                                    </td>

                                    {{-- WO / Parte --}}
                                    <td class="px-4 py-3">
                                        <div class="text-gray-900 dark:text-white font-medium">
                                            {{ $lot->workOrder?->wo_number ?? '—' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $lot->workOrder?->purchaseOrder?->part?->number ?? '—' }}
                                        </div>
                                    </td>

                                    {{-- WO Externo --}}
                                    <td class="px-4 py-3">
                                        @if($hasExternalWo)
                                            <span class="font-mono text-gray-700 dark:text-gray-300">
                                                {{ $lot->workOrder->external_wo_number }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400 text-xs font-medium">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                Sin numero externo
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Qty empacada --}}
                                    <td class="px-4 py-3 text-right">
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($lot->quantity_packed_final ?? 0) }}
                                        </span>
                                        <span class="text-xs text-gray-400 ml-1">pzs</span>
                                    </td>

                                    {{-- Tipo de cierre --}}
                                    <td class="px-4 py-3">
                                        @php
                                            $typeLabel = match($lot->closed_by_type) {
                                                'complete_lot' => ['label' => 'Completo', 'color' => 'green'],
                                                'new_lot'      => ['label' => 'Nuevo lote', 'color' => 'blue'],
                                                'close_as_is'  => ['label' => 'Tal cual', 'color' => 'gray'],
                                                default        => ['label' => '—', 'color' => 'gray'],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $typeLabel['color'] }}-100 text-{{ $typeLabel['color'] }}-800 dark:bg-{{ $typeLabel['color'] }}-900/30 dark:text-{{ $typeLabel['color'] }}-300">
                                            {{ $typeLabel['label'] }}
                                        </span>
                                    </td>

                                    {{-- Fecha de cierre --}}
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                        {{ $lot->ready_for_shipping_at?->format('d/m/Y H:i') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginacion --}}
                @if($lotsInQueue->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $lotsInQueue->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- MODAL: Crear Packing Slip                                     --}}
    {{-- ============================================================ --}}
    @if($showCreatePsModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-data
            x-init="document.body.style.overflow = 'hidden'"
            x-destroy="document.body.style.overflow = ''"
        >
            {{-- Overlay --}}
            <div
                class="absolute inset-0 bg-black/50 dark:bg-black/70"
                wire:click="cancelCreatePs"
            ></div>

            {{-- Panel --}}
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">

                {{-- Header del modal --}}
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Crear Packing Slip</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Se creara en estado Borrador con {{ count($selectedLotIds) }} lote(s)
                        </p>
                    </div>
                    <button
                        wire:click="cancelCreatePs"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Cuerpo del modal --}}
                <div class="p-6 space-y-6">

                    {{-- Tabla de lotes seleccionados --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Lotes incluidos</h3>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Lote</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Parte</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 dark:text-gray-400">Qty</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">
                                            Label Spec
                                            <span class="font-normal text-gray-400">(opcional)</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    @foreach($selectedLots as $lot)
                                        <tr>
                                            <td class="px-4 py-2.5">
                                                <span class="font-mono font-medium text-gray-900 dark:text-white text-xs">
                                                    {{ $lot->lot_number }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 text-xs">
                                                {{ $lot->workOrder?->purchaseOrder?->part?->number ?? '—' }}
                                            </td>
                                            <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white font-medium text-xs">
                                                {{ number_format($lot->quantity_packed_final ?? 0) }}
                                            </td>
                                            <td class="px-4 py-2.5">
                                                {{-- Ingreso manual de label_spec por lote (decision D-06-02) --}}
                                                <input
                                                    wire:model="labelSpecs.{{ $lot->id }}"
                                                    type="text"
                                                    placeholder="Ej: M83519/2-8"
                                                    maxlength="50"
                                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-transparent"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Notas del PS --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Notas del Packing Slip
                            <span class="font-normal text-gray-400">(opcional)</span>
                        </label>
                        <textarea
                            wire:model="psNotes"
                            rows="3"
                            placeholder="Instrucciones especiales de envio, observaciones..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                        ></textarea>
                    </div>

                    {{-- Aviso sobre el estado draft --}}
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 text-xs text-blue-700 dark:text-blue-300">
                        El Packing Slip se creara en estado <strong>Borrador</strong>. Podras revisarlo y confirmarlo antes de despacharlo.
                    </div>

                    {{-- Error en modal --}}
                    @if($errorMessage)
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 text-xs text-red-700 dark:text-red-300">
                            {{ $errorMessage }}
                        </div>
                    @endif
                </div>

                {{-- Footer del modal --}}
                <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
                    <button
                        wire:click="cancelCreatePs"
                        class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="createPackingSlip"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
                    >
                        <span wire:loading.remove wire:target="createPackingSlip">Crear Packing Slip</span>
                        <span wire:loading wire:target="createPackingSlip">Creando...</span>
                        <svg wire:loading wire:target="createPackingSlip" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
