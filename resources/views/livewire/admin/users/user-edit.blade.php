<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Editar Usuario</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Modifica la información del usuario</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg">
        <form wire:submit="updateUser" class="divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Información Personal -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Información Personal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            wire:model="name" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                            required 
                        />
                        @error('name') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Apellido</label>
                        <input 
                            type="text" 
                            wire:model="last_name" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                        />
                        @error('last_name') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información de Cuenta -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Información de Cuenta</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cuenta</label>
                        <input 
                            type="text" 
                            wire:model="account" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                        />
                        @error('account') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            wire:model="email" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                            required 
                        />
                        @error('email') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Rol y Asignación -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Rol y Asignación</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Rol <span class="text-red-500">*</span>
                        </label>
                        <select 
                            wire:model.live="selected_role" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900"
                        >
                            <option value="">Seleccionar</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('selected_role') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Departamento</label>
                        <select 
                            wire:model.live="department_id" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900"
                        >
                            <option value="">Seleccionar</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') 
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Área
                            @if($selected_role === 'Supervisor') 
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <select 
                            wire:model="area_id" 
                            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900 disabled:opacity-50" 
                            @if(!$department_id) disabled @endif
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
                </div>
            </div>

            <!-- Contraseña -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Contraseña</h3>
                <div class="mb-4">
                    <label class="flex items-center gap-2">
                        <input 
                            type="checkbox" 
                            wire:model.live="changePassword" 
                            class="w-4 h-4 text-blue-900 border-gray-300 rounded focus:ring-blue-900"
                        />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Cambiar contraseña</span>
                    </label>
                </div>
                @if($changePassword)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nueva Contraseña <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                wire:model="password" 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                            />
                            @error('password') 
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Confirmar <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                wire:model="password_confirmation" 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900" 
                            />
                            @error('password_confirmation') 
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                            @enderror
                        </div>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex justify-end gap-3">
                    <a 
                        href="{{ route('admin.users.index') }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Cancelar
                    </a>
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-sm font-medium bg-blue-900 hover:bg-blue-800 text-white rounded-md"
                    >
                        Actualizar Usuario
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
