<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Precio</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Actualiza la información del precio</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.prices.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver a la lista
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <form wire:submit="updatePrice" class="space-y-6">
                    <!-- Parte -->
                    <div>
                        <label for="part_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Parte <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="part_id" id="part_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" required>
                            <option value="">Seleccione una parte</option>
                            @foreach ($parts as $part)
                                <option value="{{ $part->id }}">{{ $part->number }} - {{ $part->description }}</option>
                            @endforeach
                        </select>
                        @error('part_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        
                        @if($has_conflict)
                            <div class="mt-2 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <span class="text-sm text-red-700 dark:text-red-300">{{ $validation_message }}</span>
                                </div>
                            </div>
                        @elseif($has_existing_prices && $info_message)
                            <div class="mt-2 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm text-blue-700 dark:text-blue-300">{{ $info_message }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Precio de Muestra y Fecha -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="sample_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Precio de Muestra <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                <input wire:model="sample_price" id="sample_price" type="number" step="0.0001" min="0"
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" required />
                            </div>
                            @error('sample_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="effective_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha Efectiva <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="effective_date" id="effective_date" type="date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" required />
                            @error('effective_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- Tipo de Estación -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Tipo de Estación de Trabajo</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach ($workstationTypes as $value => $label)
                                <label class="relative flex cursor-pointer rounded-lg border p-4 {{ $workstation_type === $value ? 'border-blue-500 ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="radio" wire:model.live="workstation_type" value="{{ $value }}" class="sr-only">
                                    <span class="flex flex-col">
                                        <span class="text-sm font-medium {{ $workstation_type === $value ? 'text-blue-900 dark:text-blue-100' : 'text-gray-900 dark:text-white' }}">{{ $label }}</span>
                                        <span class="text-xs {{ $workstation_type === $value ? 'text-blue-700 dark:text-blue-300' : 'text-gray-500' }}">
                                            {{ count(\App\Models\Price::getTierConfigForType($value)) }} niveles
                                        </span>
                                    </span>
                                    @if ($workstation_type === $value)
                                        <svg class="h-5 w-5 text-blue-600 ml-auto" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Niveles de Precio (dinámicos según tipo) -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Niveles de Precio por Volumen</h3>
                        <p class="text-sm text-gray-500 mb-4">Configure precios según la cantidad ordenada (opcional)</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($tierConfig as $index => $tier)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ $tier['label'] }} unidades
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                        <input wire:model="tier_prices.{{ $index }}" type="number" step="0.0001" min="0"
                                            placeholder="0.0000"
                                            class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Activo y Comentarios -->
                    <div class="flex items-center">
                        <input wire:model.live="active" id="active" type="checkbox" class="w-4 h-4 text-blue-600 rounded" />
                        <label for="active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Precio activo</label>
                    </div>

                    <div>
                        <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios</label>
                        <textarea wire:model="comments" id="comments" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white resize-none"></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('admin.prices.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Metadatos -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500">Creado</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $price->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500">Última actualización</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $price->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>
