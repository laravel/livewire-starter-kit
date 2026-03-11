<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.roles.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Crear Nuevo Rol</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crea un nuevo rol y asigna los permisos correspondientes</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
        <form wire:submit="saveRole" class="divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Información Básica -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Información Básica</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nombre del Rol <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        wire:model="name" 
                        placeholder="Ej: Editor, Moderador, etc."
                        class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                    @error('name') 
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <!-- Permissions -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Permisos</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Selecciona los permisos que tendrá este rol
                </p>

                @if($permissions->count() > 0)
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <button 
                                type="button"
                                wire:click="$set('selectedPermissions', {{ $permissions->pluck('id')->toJson() }})"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                Seleccionar todos
                            </button>
                            <button 
                                type="button"
                                wire:click="$set('selectedPermissions', [])"
                                class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 font-medium">
                                Deseleccionar todos
                            </button>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ count($selectedPermissions) }} / {{ $permissions->count() }} seleccionados
                            </span>
                        </div>

                        @php
                            $groupColors = [
                                'admin' => 'blue',
                                'usuarios' => 'purple',
                                'catalogos' => 'amber',
                                'ordenes' => 'indigo',
                                'produccion' => 'green',
                                'calidad' => 'teal',
                                'materiales' => 'orange',
                                'otros' => 'gray',
                            ];
                        @endphp

                        <div class="space-y-3">
                            @foreach($groupedPermissions as $group => $groupPerms)
                                @php
                                    $color = $groupColors[$group] ?? 'gray';
                                    $label = $groupLabels[$group] ?? ucfirst($group);
                                    $groupPermIds = $groupPerms->pluck('id')->toArray();
                                    $selectedInGroup = count(array_intersect($selectedPermissions, $groupPermIds));
                                    $totalInGroup = count($groupPermIds);
                                @endphp
                                <div x-data="{ open: false }" class="border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between px-4 py-3 bg-{{ $color }}-50 dark:bg-{{ $color }}-900/20 hover:bg-{{ $color }}-100 dark:hover:bg-{{ $color }}-900/30 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-3 h-3 rounded-full bg-{{ $color }}-500"></div>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $label }}</span>
                                            <span class="text-xs px-2 py-0.5 rounded-full border-2 border-{{ $color }}-200 dark:border-{{ $color }}-600 bg-{{ $color }}-100 dark:bg-{{ $color }}-900/50 text-{{ $color }}-700 dark:text-{{ $color }}-300">
                                                {{ $selectedInGroup }}/{{ $totalInGroup }}
                                            </span>
                                        </div>
                                        <svg :class="{ 'rotate-180': open }" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="open" x-collapse class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($groupPerms as $permission)
                                                @php
                                                    $shortName = str_replace($group . '.', '', $permission->name);
                                                @endphp
                                                <div class="flex items-center">
                                                    <input 
                                                        type="checkbox" 
                                                        id="perm_create_{{ $permission->id }}"
                                                        wire:model="selectedPermissions" 
                                                        value="{{ $permission->id }}"
                                                        class="h-4 w-4 text-{{ $color }}-600 focus:ring-{{ $color }}-500 border-2 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                                                    >
                                                    <label for="perm_create_{{ $permission->id }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                                        {{ $shortName }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No hay permisos disponibles</h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            Primero debes crear algunos permisos antes de asignarlos a roles.
                        </p>
                    </div>
                @endif

                @error('selectedPermissions') 
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> 
                @enderror
            </div>

            <!-- Actions -->
            <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex justify-end gap-3">
                    <a 
                        href="{{ route('admin.roles.index') }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Cancelar
                    </a>
                    <button 
                        type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Crear Rol
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
