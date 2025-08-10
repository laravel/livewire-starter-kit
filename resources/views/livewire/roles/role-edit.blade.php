<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Volt\Component;

new class extends Component {
    public Role $role;
    public string $name = '';
    public array $selectedPermissions = [];

    public function mount(Role $role): void
    {
        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
    }

    public function render(): mixed
    {
        $permissions = Permission::orderBy('name')->get();

        return view('livewire.roles.role-edit', [
            'permissions' => $permissions,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $this->role->id,
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'exists:permissions,id',
        ];
    }

    public function updateRole(): void
    {
        $this->validate();

        $this->role->update(['name' => $this->name]);
        
        // Validar que los permisos seleccionados existan en la base de datos
        $existingPermissionIds = Permission::whereIn('id', $this->selectedPermissions)->pluck('id')->toArray();
        
        // Solo sincronizar los permisos que realmente existen
        $this->role->syncPermissions($existingPermissionIds);

        session()->flash('flash.banner', 'Rol actualizado correctamente.');
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
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Rol: {{ $role->name }}</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Modifica la información del rol y sus permisos
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

        <!-- Role Info -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm text-blue-800 dark:text-blue-200">
                    Este rol está asignado a <strong>{{ $role->users()->count() }}</strong> usuario(s)
                </span>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <form wire:submit="updateRole" class="p-6 space-y-6">
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
                        <div class="space-y-4">
                            <!-- Select All / Deselect All -->
                            <div class="flex items-center space-x-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                                <button 
                                    type="button"
                                    wire:click="$set('selectedPermissions', {{ $permissions->pluck('id')->toJson() }})"
                                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    Seleccionar todos
                                </button>
                                <button 
                                    type="button"
                                    wire:click="$set('selectedPermissions', [])"
                                    class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300">
                                    Deseleccionar todos
                                </button>
                            </div>

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
                        Actualizar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>