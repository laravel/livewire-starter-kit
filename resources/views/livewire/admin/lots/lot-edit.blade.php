<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Lote</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Modificar información del lote {{ $lot->lot_number }}
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.lots.show', $lot) }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <form wire:submit="save">
                <div class="p-6 space-y-6">
                    <!-- Work Order Info (Read-only) -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                        <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">Work Order</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-blue-700 dark:text-blue-300 font-medium">WO:</span>
                                <span class="text-blue-900 dark:text-blue-100 ml-2">{{ $lot->workOrder->purchaseOrder->wo ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-blue-700 dark:text-blue-300 font-medium">Parte:</span>
                                <span class="text-blue-900 dark:text-blue-100 ml-2">{{ $lot->workOrder->purchaseOrder->part->number }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lot Number -->
                    <div>
                        <label for="lot_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Número de Lote/Viajero <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="lot_number"
                            wire:model="lot_number"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500"
                            placeholder="Ej: 001, LOT-2024-001"
                        >
                        @error('lot_number')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Cantidad <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="quantity"
                            wire:model="quantity"
                            min="1"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500"
                            placeholder="Ej: 100"
                        >
                        @error('quantity')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <a href="{{ route('admin.lots.show', $lot) }}"
                        class="inline-flex justify-center items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors duration-200">
                        Cancelar
                    </a>
                    <button 
                        type="submit"
                        class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
