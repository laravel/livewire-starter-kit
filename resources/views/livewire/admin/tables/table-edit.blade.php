<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.tables.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Editar Mesa</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Modifica la información de la mesa {{ $table->number }}</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
        <form wire:submit="update" class="divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Información Básica -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Información Básica</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Número <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="number"
                            type="text"
                            class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            placeholder="Número de la mesa"
                            required
                        >
                        @error('number')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nombre
                        </label>
                        <input
                            wire:model="name"
                            type="text"
                            class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            placeholder="Ej: Mesa de Ensamble Principal"
                        >
                        @error('name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Área <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model="area_id"
                            class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            required
                        >
                            <option value="">Selecciona un área</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                        @error('area_id')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Estado de Producción <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model="production_status_id"
                            class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            required
                        >
                            <option value="">Selecciona un estado</option>
                            @foreach($productionStatuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                        @error('production_status_id')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Número de Empleados
                        </label>
                        <input
                            wire:model="employees"
                            type="number"
                            min="1"
                            class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            placeholder="Ej: 2"
                        >
                        @error('employees')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center">
                            <input
                                wire:model="active"
                                type="checkbox"
                                class="rounded border-2 border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-700"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Mesa activa</span>
                        </label>
                        @error('active')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información del Equipo -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Información del Equipo</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Marca
                        </label>
                        <input
                            wire:model="brand"
                            type="text"
                            class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            placeholder="Ej: FANUC, ABB, etc."
                        >
                        @error('brand')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Modelo
                        </label>
                        <input
                            wire:model="model"
                            type="text"
                            class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            placeholder="Ej: XR-1000"
                        >
                        @error('model')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Número de Serie
                        </label>
                        <input
                            wire:model="s_n"
                            type="text"
                            class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            placeholder="Ej: SN-12345678"
                        >
                        @error('s_n')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Número de Activo
                        </label>
                        <input
                            wire:model="asset_number"
                            type="text"
                            class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            placeholder="Ej: AST-123456"
                        >
                        @error('asset_number')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Descripción y Comentarios -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Descripción y Comentarios</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Descripción
                        </label>
                        <textarea
                            wire:model="description"
                            rows="3"
                            class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            placeholder="Descripción detallada de la mesa..."
                        ></textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Comentarios adicionales
                        </label>
                        <textarea
                            wire:model="comments"
                            rows="3"
                            class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            placeholder="Comentarios adicionales sobre la mesa..."
                        ></textarea>
                        @error('comments')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex justify-end gap-3">
                    <a 
                        href="{{ route('admin.tables.index') }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Cancelar
                    </a>
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-md"
                    >
                        Actualizar Mesa
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
