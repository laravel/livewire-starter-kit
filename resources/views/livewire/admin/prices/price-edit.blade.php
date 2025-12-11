<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Precio</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Actualiza la información del precio
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    @if (Route::has('admin.prices.index'))
                        <a href="{{ route('admin.prices.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver a la lista
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card Container -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <form wire:submit="updatePrice" class="space-y-6">
                    <!-- Parte -->
                    <div>
                        <label for="part_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Parte <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="part_id" id="part_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                            <option value="">Seleccione una parte</option>
                            @foreach ($parts as $part)
                                <option value="{{ $part->id }}">
                                    {{ $part->number }} - {{ $part->description }}
                                </option>
                            @endforeach
                        </select>
                        @error('part_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Precio Unitario y Fecha Efectiva -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Precio Unitario <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400">$</span>
                                </div>
                                <input wire:model="unit_price" id="unit_price" type="number" step="0.0001" min="0"
                                    placeholder="0.0000"
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    required />
                            </div>
                            @error('unit_price')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="effective_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha Efectiva <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="effective_date" id="effective_date" type="date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                required />
                            @error('effective_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Niveles de Precio por Volumen -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Niveles de Precio por Volumen
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Configure precios especiales según la cantidad ordenada (opcional)
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="tier_1_999" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nivel 1-999 unidades
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400">$</span>
                                    </div>
                                    <input wire:model="tier_1_999" id="tier_1_999" type="number" step="0.0001" min="0"
                                        placeholder="0.0000"
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                                </div>
                                @error('tier_1_999')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="tier_1000_10999" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nivel 1,000-10,999 unidades
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400">$</span>
                                    </div>
                                    <input wire:model="tier_1000_10999" id="tier_1000_10999" type="number" step="0.0001" min="0"
                                        placeholder="0.0000"
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                                </div>
                                @error('tier_1000_10999')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="tier_11000_99999" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nivel 11,000-99,999 unidades
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400">$</span>
                                    </div>
                                    <input wire:model="tier_11000_99999" id="tier_11000_99999" type="number" step="0.0001" min="0"
                                        placeholder="0.0000"
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                                </div>
                                @error('tier_11000_99999')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="tier_100000_plus" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nivel 100,000+ unidades
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400">$</span>
                                    </div>
                                    <input wire:model="tier_100000_plus" id="tier_100000_plus" type="number" step="0.0001" min="0"
                                        placeholder="0.0000"
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                                </div>
                                @error('tier_100000_plus')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Estado Activo -->
                    <div class="flex items-center">
                        <input wire:model="active" id="active" type="checkbox"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
                        <label for="active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Precio activo
                        </label>
                    </div>

                    <!-- Comentarios -->
                    <div>
                        <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Comentarios
                        </label>
                        <textarea wire:model="comments" id="comments" rows="3"
                            placeholder="Ingrese comentarios adicionales sobre este precio..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 resize-none"></textarea>
                        @error('comments')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('admin.prices.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200">
                            Cancelar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Metadatos -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Creado</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $price->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Última actualización</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $price->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
