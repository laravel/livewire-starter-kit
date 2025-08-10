<?php

use Spatie\Permission\Models\Permission;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name',
        ];
    }

    public function save(): void
    {
        $this->validate();

        Permission::create([
            'name' => $this->name,
            'guard_name' => 'web',
        ]);

        session()->flash('flash.banner', 'Permiso creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('permissions.index'));
    }

    public function render(): mixed
    {
        return view('livewire.permissions.permission-create');
    }
};

?>

<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('permissions.index') }}" 
                   class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Volver a permisos
                </a>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">Crear Nuevo Permiso</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Crea un nuevo permiso para el sistema
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <form wire:submit="save" class="p-6 space-y-6">
                <!-- Permission Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nombre del Permiso <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="name"
                            wire:model="name"
                            placeholder="Ej: create-users, edit-posts, view-reports"
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Usa un formato descriptivo como "accion-recurso" (ej: create-users, edit-posts)
                    </p>
                </div>

                <!-- Permission Guidelines -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                Convenciones para nombres de permisos
                            </h3>
                            <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Usa guiones para separar palabras: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">create-users</code></li>
                                    <li>Sigue el patrón acción-recurso: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">edit-posts</code></li>
                                    <li>Usa minúsculas: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">view-reports</code></li>
                                    <li>Sé específico: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">delete-own-posts</code></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('permissions.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
                        Cancelar
                    </a>
                    <button 
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Crear Permiso
                    </button>
                </div>
            </form>
        </div>

        <!-- Examples Section -->
        <div class="mt-8 bg-gray-50 dark:bg-gray-900 rounded-xl p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Ejemplos de permisos comunes</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gestión de usuarios</h4>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <li><code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">create-users</code></li>
                        <li><code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">edit-users</code></li>
                        <li><code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">delete-users</code></li>
                        <li><code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">view-users</code></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reportes</h4>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <li><code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">view-reports</code></li>
                        <li><code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">export-reports</code></li>
                        <li><code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">create-reports</code></li>
                        <li><code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">delete-reports</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>