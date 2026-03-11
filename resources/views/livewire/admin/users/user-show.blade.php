<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $user->name }} {{ $user->last_name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                Volver
            </a>
        </div>
    </div>

    <!-- Information Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Información Personal -->
        <div class="bg-white dark:bg-gray-800 rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Información Personal</h3>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nombre Completo</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $user->name }} {{ $user->last_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Correo Electrónico</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $user->email }}</dd>
                </div>
                @if($user->account)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Número de Cuenta</dt>
                        <dd class="text-sm font-mono text-gray-900 dark:text-white">{{ $user->account }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de Registro</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $user->created_at->format('d/m/Y') }}</dd>
                </div>
            </div>
        </div>

        <!-- Información Laboral -->
        <div class="bg-white dark:bg-gray-800 rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Información Laboral</h3>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Rol</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        @if($user->roles->isNotEmpty())
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $user->roles->first()->name }}
                            </span>
                        @else
                            <span class="text-gray-400">Sin rol asignado</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Área Asignada</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        @if($user->areas->isNotEmpty())
                            @foreach($user->areas as $area)
                                <div class="font-medium">{{ $area->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $area->department->name }}</div>
                            @endforeach
                        @else
                            <span class="text-gray-400">Sin área asignada</span>
                        @endif
                    </dd>
                </div>
            </div>
        </div>
    </div>
</div>
