<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.lots.show', $lot) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Editar Lote</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Modificar información del lote {{ $lot->lot_number }}</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
        <form wire:submit="save" class="divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Work Order Info (Read-only) -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Información de Work Order</h3>
                <div class="bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-lg p-4">
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
            </div>

            <!-- Lot Information -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Información del Lote</h3>
                
                <!-- Lot Number -->
                <div class="mb-4">
                    <label for="lot_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Número de Lote/Viajero <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="lot_number"
                        wire:model="lot_number"
                        class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        placeholder="Ej: 001, LOT-2024-001"
                    >
                    @error('lot_number')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
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
                        class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        placeholder="Ej: 100"
                    >
                    @error('quantity')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.lots.show', $lot) }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancelar
                    </a>
                    <button 
                        type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
