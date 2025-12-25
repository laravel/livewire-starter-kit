<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.over-times.index') }}"
                   wire:navigate
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Nuevo Over Time</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Registra un nuevo tiempo extra en el sistema
                    </p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <form wire:submit="save" class="p-6 space-y-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informacion Basica</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input
                                wire:model="name"
                                type="text"
                                id="name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Nombre del over time"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha <span class="text-red-500">*</span>
                            </label>
                            <input
                                wire:model="date"
                                type="date"
                                id="date"
                                min="{{ now()->toDateString() }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                            @error('date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="shift_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Turno <span class="text-red-500">*</span>
                            </label>
                            <select
                                wire:model="shift_id"
                                id="shift_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                                <option value="">Selecciona un turno</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                            @error('shift_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Time Information -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informacion de Horario</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Hora Inicio <span class="text-red-500">*</span>
                            </label>
                            <input
                                wire:model.blur="start_time"
                                type="time"
                                id="start_time"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                            @error('start_time')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Hora Fin <span class="text-red-500">*</span>
                            </label>
                            <input
                                wire:model.blur="end_time"
                                type="time"
                                id="end_time"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                            @error('end_time')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="break_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Descanso (minutos) <span class="text-red-500">*</span>
                            </label>
                            <input
                                wire:model.blur="break_minutes"
                                type="number"
                                id="break_minutes"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="0"
                            >
                            @error('break_minutes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Employee Information -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informacion de Empleados</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="employees_qty" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cantidad de Empleados <span class="text-red-500">*</span>
                            </label>
                            <input
                                wire:model.blur="employees_qty"
                                type="number"
                                id="employees_qty"
                                min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="1"
                            >
                            @error('employees_qty')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Preview Calculation -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                            <h4 class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2">Preview de Calculos</h4>
                            <div class="space-y-1 text-sm text-blue-800 dark:text-blue-200">
                                <div class="flex justify-between">
                                    <span>Horas Netas:</span>
                                    <span class="font-semibold">{{ $this->net_hours }} hrs</span>
                                </div>
                                <div class="flex justify-between border-t border-blue-200 dark:border-blue-700 pt-1">
                                    <span>Horas Totales:</span>
                                    <span class="font-semibold">{{ $this->total_hours }} hrs</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Comentarios</h3>
                    <div>
                        <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Comentarios Adicionales
                        </label>
                        <textarea
                            wire:model="comments"
                            id="comments"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Comentarios adicionales sobre el over time..."
                        ></textarea>
                        @error('comments')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Actions -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex items-center justify-end space-x-4">
                        <a href="{{ route('admin.over-times.index') }}"
                           wire:navigate
                           class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </a>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Crear Over Time
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
