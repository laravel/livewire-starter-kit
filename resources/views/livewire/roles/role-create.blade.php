<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public array $selectedPermissions = [];

    public function render(): mixed
    {
        $permissions = Permission::orderBy('name')->get();

        return view('livewire.roles.role-create', [
            'permissions' => $permissions,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name',
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'exists:permissions,id',
        ];
    }

    public function saveRole(): void
    {
        $this->validate();

        $role = Role::create(['name' => $this->name]);

        if (!empty($this->selectedPermissions)) {
            // Validar que los permisos seleccionados existan en la base de datos
            $existingPermissionIds = Permission::whereIn('id', $this->selectedPermissions)->pluck('id')->toArray();
            
            // Solo sincronizar los permisos que realmente existen
            $role->syncPermissions($existingPermissionIds);
        }

        session()->flash('flash.banner', 'Rol creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        redirect()->route('roles.index');
    }
};

?>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Crear Nuevo Rol</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Crea un nuevo rol y asigna los permisos correspondientes
                    </p>
                </div>
                <a href="{{ route('roles.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <form wire:submit="saveRole" class="p-6 space-y-6">
                <!-- Basic Information -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Información Básica</h3>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nombre del Rol *
                        </label>
                        <input 
                            type="text" 
                            id="name"
                            wire:model="name" 
                            placeholder="Ej: Editor, Moderador, etc."
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                        >
                        @error('name') 
                            <span class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                <!-- Permissions -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Permisos</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Selecciona los permisos que tendrá este rol
                    </p>

                    @if($permissions->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($permissions as $permission)
                                <div class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        id="permission_{{ $permission->id }}"
                                        wire:model="selectedPermissions" 
                                        value="{{ $permission->id }}"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                                    >
                                    <label for="permission_{{ $permission->id }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            @endforeach
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
                        <span class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('roles.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200">
                        Cancelar
                    </a>
                    <button 
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Crear Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>