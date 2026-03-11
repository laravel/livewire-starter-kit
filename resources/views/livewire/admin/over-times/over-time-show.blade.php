<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.over-times.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $overTime->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $overTime->date->format('d/m/Y') }}
                    @if($overTime->isToday())
                        <span class="ml-1 px-2 py-0.5 text-xs font-medium rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">Hoy</span>
                    @elseif($overTime->isFuture())
                        <span class="ml-1 px-2 py-0.5 text-xs font-medium rounded-full bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">Próximo</span>
                    @else
                        <span class="ml-1 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Completado</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.over-times.edit', $overTime) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            <a href="{{ route('admin.over-times.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Información general -->
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Información general</h3>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nombre</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $overTime->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $overTime->date->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Turno</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $overTime->shift->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cantidad de empleados</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $overTime->employees_qty }} empleado{{ $overTime->employees_qty > 1 ? 's' : '' }}</dd>
                </div>
            </div>
        </div>

        <!-- Horarios y cálculos -->
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Horarios y cálculos</h3>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Horario</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($overTime->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($overTime->end_time)->format('H:i') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Descanso</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $overTime->break_minutes }} minutos</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Horas netas</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $overTime->net_hours }} hrs</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Horas totales</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $overTime->total_hours }} hrs</dd>
                </div>
            </div>
        </div>
    </div>

    @if($overTime->comments)
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Comentarios</h3>
            </div>
            <div class="p-4">
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $overTime->comments }}</p>
            </div>
        </div>
    @endif

    <!-- Registro -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Registro</h3>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de creación</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $overTime->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Última actualización</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $overTime->updated_at->format('d/m/Y H:i') }}</dd>
            </div>
        </div>
    </div>
</div>
