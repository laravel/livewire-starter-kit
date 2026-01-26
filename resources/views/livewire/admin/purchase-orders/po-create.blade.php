<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6 sm:py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <div class="flex items-center gap-3 sm:gap-4 mb-2">
                <a href="{{ route('admin.purchase-orders.index') }}" 
                   class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-colors">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Nueva Orden de Compra</h1>
                        <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Ingrese la información de la nueva PO</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <form wire:submit="savePO" class="divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Información Básica -->
                <div class="p-4 sm:p-6 lg:p-8">
                    <div class="mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            Información Básica
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Datos principales de la orden de compra</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                        <div>
                            <label for="po_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Número de PO <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="po_number" id="po_number" type="text" placeholder="Ej: PO-2025-001"
                                class="block w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                required />
                            @error('po_number')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="wo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                WO
                            </label>
                            <input wire:model="wo" id="wo" type="text" placeholder="Ej: WO-2025-001"
                                class="block w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors" />
                            @error('wo')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="part_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Parte <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="part_id" id="part_id"
                                class="block w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                required>
                                <option value="">Seleccione una parte</option>
                                @foreach($parts as $part)
                                    <option value="{{ $part->id }}">{{ $part->number }} - {{ Str::limit($part->description, 40) }}</option>
                                @endforeach
                            </select>
                            @error('part_id')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Fechas -->
                <div class="p-4 sm:p-6 lg:p-8">
                    <div class="mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            Fechas
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Fechas importantes de la orden</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="po_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha de PO <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="po_date" id="po_date" type="date"
                                class="block w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                required />
                            @error('po_date')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha de Entrega <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="due_date" id="due_date" type="date"
                                class="block w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                required />
                            @error('due_date')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Cantidad y Precio -->
                <div class="p-4 sm:p-6 lg:p-8">
                    <div class="mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                            Cantidad y Precio
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Detalles de cantidad y precio unitario</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cantidad <span class="text-red-500">*</span>
                            </label>
                            <input wire:model.live.debounce.500ms="quantity" id="quantity" type="number" min="1"
                                class="block w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                required />
                            @error('quantity')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Precio Unitario <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 dark:text-gray-400">$</span>
                                <input wire:model.live.debounce.500ms="unit_price" id="unit_price" type="number" step="0.0001" min="0"
                                    class="block w-full pl-7 px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                    required />
                            </div>
                            @error('unit_price')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Price Validation Feedback -->
                    @if($price_message)
                        <div class="mt-4 p-4 rounded-lg {{ $price_valid ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800' }}">
                            <div class="flex items-start gap-2">
                                @if($price_valid)
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-orange-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm {{ $price_valid ? 'text-green-700 dark:text-green-300' : 'text-orange-700 dark:text-orange-300' }} font-medium">
                                        {{ $price_message }}
                                    </span>
                                    @if($expected_price !== null)
                                        <p class="mt-1 text-xs {{ $price_valid ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                            Precio esperado para cantidad {{ number_format($quantity) }}: ${{ number_format($expected_price, 4) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Archivo PDF -->
                <div class="p-4 sm:p-6 lg:p-8">
                    <div class="mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            Documento
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Archivo PDF de la orden de compra</p>
                    </div>
                    <div>
                        <label for="pdf_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Archivo PDF de la PO <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model="pdf_file" id="pdf_file" type="file" accept=".pdf"
                                class="block w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors" />
                            <div wire:loading wire:target="pdf_file" class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-gray-700/80 rounded-lg backdrop-blur-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Cargando...</span>
                                </div>
                            </div>
                        </div>
                        @if($pdf_file)
                            <div class="mt-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                <div class="flex items-center text-green-700 dark:text-green-300">
                                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm truncate">{{ $pdf_file->getClientOriginalName() }}</span>
                                </div>
                            </div>
                        @endif
                        @error('pdf_file')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Máximo 10MB, solo archivos PDF</p>
                    </div>
                </div>

                <!-- Comentarios -->
                <div class="p-4 sm:p-6 lg:p-8">
                    <div class="mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                            <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                            Comentarios
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Información adicional (opcional)</p>
                    </div>
                    <div>
                        <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Comentarios
                        </label>
                        <textarea wire:model="comments" id="comments" rows="4"
                            placeholder="Comentarios adicionales..."
                            class="block w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 resize-none transition-colors"></textarea>
                        @error('comments')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Actions -->
                <div class="p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900/50">
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
                        <a href="{{ route('admin.purchase-orders.index') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors border border-gray-300 dark:border-gray-600">
                            Cancelar
                        </a>

                        <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
