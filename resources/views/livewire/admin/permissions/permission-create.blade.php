<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.permissions.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Crear Nuevo Permiso</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crea un nuevo permiso para el sistema</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
        <form wire:submit="save" class="divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Permission Name -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Información del Permiso</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nombre del Permiso <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model="name"
                            placeholder="Ej: create-users, edit-posts, view-reports"
                            class="block w-full pl-10 pr-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            required
                        >
                    </div>
                    @error('name')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Usa un formato descriptivo como "accion-recurso" (ej: create-users, edit-posts)
                    </p>
                </div>
            </div>

            <!-- Permission Guidelines -->
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Convenciones para nombres de permisos</h3>
                <div class="bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm text-blue-700 dark:text-blue-300">
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
            </div>

            <!-- Actions -->
            <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex justify-end gap-3">
                    <a 
                        href="{{ route('admin.permissions.index') }}" 
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
                        Crear Permiso
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Examples Section -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Ejemplos de permisos comunes</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gestión de usuarios</h4>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded border-2 border-gray-200 dark:border-gray-600">create-users</code></li>
                    <li><code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded border-2 border-gray-200 dark:border-gray-600">edit-users</code></li>
                    <li><code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded border-2 border-gray-200 dark:border-gray-600">delete-users</code></li>
                    <li><code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded border-2 border-gray-200 dark:border-gray-600">view-users</code></li>
                </ul>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reportes</h4>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li><code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded border-2 border-gray-200 dark:border-gray-600">view-reports</code></li>
                    <li><code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded border-2 border-gray-200 dark:border-gray-600">export-reports</code></li>
                    <li><code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded border-2 border-gray-200 dark:border-gray-600">create-reports</code></li>
                    <li><code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded border-2 border-gray-200 dark:border-gray-600">delete-reports</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
