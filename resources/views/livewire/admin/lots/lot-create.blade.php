<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Nuevo Lote</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Crear un nuevo lote de producción
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.lots.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <form wire:submit="save" class="p-6 space-y-6">
                <!-- Work Order Selection -->
                <div>
                    <label for="work_order_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Work Order <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="work_order_id" id="work_order_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Seleccionar Work Order</option>
                        @foreach($workOrders as $wo)
                            <option value="{{ $wo->id }}">
                                {{ $wo->wo_number }} - {{ $wo->purchaseOrder->part->number }} (Pendiente: {{ number_format($wo->pending_quantity) }})
                            </option>
                        @endforeach
                    </select>
                    @error('work_order_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Selected WO Info -->
                @if($selectedWorkOrder)
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Información de la Work Order</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Parte</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedWorkOrder->purchaseOrder->part->number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Cantidad Original</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($selectedWorkOrder->original_quantity) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Enviadas</p>
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ number_format($selectedWorkOrder->sent_pieces) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pendiente</p>
                            <p class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ number_format($selectedWorkOrder->pending_quantity) }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Cantidad <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="quantity" id="quantity" min="1"
                        @if($selectedWorkOrder) max="{{ $selectedWorkOrder->pending_quantity }}" @endif
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Cantidad de piezas para este lote">
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @if($selectedWorkOrder)
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Máximo disponible: {{ number_format($selectedWorkOrder->pending_quantity) }} piezas
                        </p>
                    @endif
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Descripción
                    </label>
                    <textarea wire:model="description" id="description" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Descripción del lote (opcional)"></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Comments -->
                <div>
                    <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Comentarios
                    </label>
                    <textarea wire:model="comments" id="comments" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Comentarios adicionales (opcional)"></textarea>
                    @error('comments')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info Box -->
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                El número de lote se generará automáticamente de forma secuencial (001, 002, 003...) basado en la Work Order seleccionada.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.lots.index') }}"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm">
                        Crear Lote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
