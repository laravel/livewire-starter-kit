<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.employees.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Editar Empleado</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Modifica la información del empleado</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg">
        <form wire:submit="save" class="divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Información Personal -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Información Personal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input 
                            wire:model="name" 
                            type="text" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                        />
                        @error('name') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Apellido <span class="text-red-500">*</span>
                        </label>
                        <input 
                            wire:model="last_name" 
                            type="text" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                        />
                        @error('last_name') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input 
                            wire:model="email" 
                            type="email" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                        />
                        @error('email') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número de Empleado</label>
                        <div class="px-4 py-2 text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-white font-mono">
                            {{ $employee->employee_number ?? '-' }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">El número de empleado no puede ser modificado</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha de Nacimiento</label>
                        <input 
                            wire:model="birth_date" 
                            type="date" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                        />
                        @error('birth_date') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha de Ingreso</label>
                        <input 
                            wire:model="entry_date" 
                            type="date" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                        />
                        @error('entry_date') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información Laboral -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Información Laboral</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Área <span class="text-red-500">*</span>
                        </label>
                        <select 
                            wire:model="area_id" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900"
                        >
                            <option value="">Seleccionar</option>
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
                            Turno <span class="text-red-500">*</span>
                        </label>
                        <select 
                            wire:model="shift_id" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900"
                        >
                            <option value="">Seleccionar</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                        @error('shift_id') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Posición / Cargo</label>
                        <input 
                            wire:model="position" 
                            type="text" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                        />
                        @error('position') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Contraseña -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Contraseña</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Deja estos campos vacíos si no deseas cambiar la contraseña.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nueva Contraseña</label>
                        <input 
                            wire:model="password" 
                            type="password" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                        />
                        @error('password') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirmar</label>
                        <input 
                            wire:model="password_confirmation" 
                            type="password" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                        />
                        @error('password_confirmation') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Información Adicional</h3>
                <div class="mb-4">
                    <label class="flex items-center gap-2">
                        <input 
                            wire:model="active" 
                            type="checkbox" 
                            class="w-4 h-4 text-blue-900 border-gray-300 rounded focus:ring-blue-900"
                        />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Empleado Activo</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Comentarios</label>
                    <textarea 
                        wire:model="comments" 
                        rows="3" 
                        class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900 resize-none"
                    ></textarea>
                    @error('comments') 
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex justify-end gap-3">
                    <a 
                        href="{{ route('admin.employees.show', $employee) }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Cancelar
                    </a>
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-sm font-medium bg-blue-900 hover:bg-blue-800 text-white rounded-md"
                    >
                        Actualizar Empleado
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
