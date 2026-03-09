<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Nuevo Packing Slip</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Crea un nuevo documento de empaque seleccionando los lotes disponibles
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.packing-slips.index') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>

        <form wire:submit="save">
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
                            Lotes Disponibles para Despacho
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
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-10">Sel.</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Work Order</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">PO</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item No</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
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
                                            {{-- Work Order --}}
                                            <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-white">
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
                                            {{-- PO --}}
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $lot->workOrder?->purchaseOrder?->po_number ?? '-' }}
                                            </td>
                                            {{-- Item No --}}
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $lot->workOrder?->purchaseOrder?->part?->item_number ?? '-' }}
                                            </td>
                                            {{-- Description --}}
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                                {{ $lot->workOrder?->purchaseOrder?->part?->number ?? '-' }}
                                            </td>
                                            {{-- Quantity --}}
                                            <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-white">
                                                {{ number_format($lot->quantity_packed_final ?? $lot->quantity ?? 0) }}
                                            </td>
                                            {{-- Date --}}
                                            <td class="px-4 py-3">
                                                <input
                                                    type="text"
                                                    wire:model="dateSpecs.{{ $lot->id }}"
                                                    maxlength="20"
                                                    placeholder="ej: 20250512A22"
                                                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 text-sm w-36 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                                >
                                            </td>
                                            {{-- Label Spec --}}
                                            <td class="px-4 py-3">
                                                <input
                                                    type="text"
                                                    wire:model="labelSpecs.{{ $lot->id }}"
                                                    maxlength="50"
                                                    placeholder="Label spec..."
                                                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 text-sm w-full max-w-xs focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                                >
                                                @error("labelSpecs.{$lot->id}")
                                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                @enderror
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12 bg-gray-50 dark:bg-gray-900 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="mt-4 text-sm font-medium text-gray-900 dark:text-white">No hay lotes disponibles para despacho</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Los lotes deben tener <code class="text-xs bg-gray-200 dark:bg-gray-700 px-1 rounded">ready_for_shipping = true</code> y no estar asignados a otro Packing Slip.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.packing-slips.index') }}" wire:navigate
                   class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 disabled:opacity-50"
                    @if ($availableLots->count() === 0) disabled @endif
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Crear Packing Slip
                </button>
            </div>
        </form>

    </div>
</div>
