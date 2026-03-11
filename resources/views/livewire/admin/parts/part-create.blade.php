<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.parts.index') }}" class="inline-flex items-center justify-center w-10 h-10 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-md transition-colors" title="Volver">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Crear parte</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ingrese la información de la nueva parte</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden divide-y divide-gray-200 dark:divide-gray-700">
        <form wire:submit="savePart" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número de parte <span class="text-red-500">*</span></label>
                    <input wire:model="number" id="number" type="text" placeholder="Ej: PART-001"
                        class="block w-full px-3 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        required />
                    @error('number')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="item_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número de ítem <span class="text-red-500">*</span></label>
                    <input wire:model="item_number" id="item_number" type="text" placeholder="Ej: ITEM-001"
                        class="block w-full px-3 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        required />
                    @error('item_number')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="unit_of_measure" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unidad de medida</label>
                <input wire:model="unit_of_measure" id="unit_of_measure" type="text" placeholder="Ej: PZA, KG, M"
                    class="block w-full px-3 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                @error('unit_of_measure')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descripción</label>
                <textarea wire:model="description" id="description" rows="3" placeholder="Descripción detallada de la parte..."
                    class="block w-full px-3 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"></textarea>
                @error('description')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notas</label>
                <input wire:model="notes" id="notes" type="text" placeholder="Notas adicionales..."
                    class="block w-full px-3 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                @error('notes')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-8">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input wire:model="active" type="checkbox" class="w-4 h-4 text-blue-600 border-2 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Parte activa</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input wire:model="is_crimp" type="checkbox" class="w-4 h-4 text-amber-600 border-2 border-gray-300 dark:border-gray-600 rounded focus:ring-amber-500 dark:bg-gray-700 dark:border-gray-600" />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Es CRIMP <span class="text-xs text-gray-500">(puede tener múltiples kits)</span></span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.parts.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-md transition-colors">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors">Crear parte</button>
            </div>
        </form>
    </div>
</div>
