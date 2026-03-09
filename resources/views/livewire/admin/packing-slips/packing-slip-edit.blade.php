<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Editar Packing Slip
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-mono">{{ $packingSlip->ps_number }}</span> — Solo editables en estado Borrador
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-2">
                    <a href="{{ route('admin.packing-slips.show', $packingSlip) }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>

        @if (!$packingSlip->isDraft())
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6 mb-6">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <p class="font-medium text-yellow-800 dark:text-yellow-200">Este Packing Slip no es editable</p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                            Solo se pueden editar Packing Slips en estado <strong>Borrador</strong>. El estado actual es: <strong>{{ $packingSlip->statusLabel }}</strong>
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.packing-slips.show', $packingSlip) }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        Ver Packing Slip
                    </a>
                </div>
            </div>
        @else
            <form wire:submit="update">
                <!-- Notas -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información del Packing Slip</h2>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Notas <span class="text-gray-400 font-normal">(opcional)</span>
                            </label>
                            <textarea
                                id="notes"
                                wire:model="notes"
                                rows="3"
                                maxlength="1000"
                                placeholder="Ingrese notas o comentarios adicionales..."
                                class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 w-full focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                            ></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Selección de Lotes -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Lotes del Packing Slip
                            </h2>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ count($selectedLotIds) }} seleccionado(s)
                            </span>
                        </div>

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
                    </div>
                </div>

                <!-- Acciones -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.packing-slips.show', $packingSlip) }}" wire:navigate
                       class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        Cancelar
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        @endif

    </div>
</div>
