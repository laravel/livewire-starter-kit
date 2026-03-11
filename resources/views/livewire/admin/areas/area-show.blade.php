<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.areas.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $area->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detalles del área y equipos asociados</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.areas.edit', $area) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium rounded-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            <a href="{{ route('admin.areas.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                Volver
            </a>
        </div>
    </div>

    <!-- Information Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Información del Área</h3>
        </div>
        <div class="p-4 space-y-3">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nombre</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $area->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Departamento</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $area->department->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Supervisor</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $area->supervisor_name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Descripción</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $area->description ?? 'Sin descripción' }}</dd>
            </div>
            @if($area->comments)
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Comentarios</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $area->comments }}</dd>
                </div>
            @endif
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Máquinas</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_machines'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Máquinas Activas</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['active_machines'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Mesas</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_tables'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Mesas Activas</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['active_tables'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Semi-Automáticos</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_semi_automatic'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Semi-Automáticos Activos</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['active_semi_automatic'] }}</div>
        </div>
    </div>

    <!-- Equipment Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Equipos en esta área</h3>
        </div>
        @if($area->getAllEquipment()->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($area->getAllEquipment() as $equipment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        @switch($equipment->equipment_type)
                                            @case('machine')
                                                Máquina
                                                @break
                                            @case('table')
                                                Mesa
                                                @break
                                            @case('semi_automatic')
                                                Semi-Automático
                                                @break
                                        @endswitch
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $equipment->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($equipment->active) && $equipment->active)
                                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            Activo
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay equipos asociados a esta área</p>
            </div>
        @endif
    </div>
</div>
