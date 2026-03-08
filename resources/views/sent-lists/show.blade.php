<x-layouts.admin>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Lista Preliminar #{{ $sentList->id }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detalle y flujo por departamentos</p>
            </div>
            <div class="flex items-center gap-2">
                @if ($sentList->isPending())
                    <a href="{{ route('admin.sent-lists.edit', $sentList) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-md transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Editar
                    </a>
                @endif
                <a href="{{ route('admin.sent-lists.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-lg border-2 border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3">
                <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Status & department --}}
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium
                        {{ $sentList->status === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                        {{ $sentList->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                        {{ $sentList->status === 'canceled' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                        {{ $sentList->status_label }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        {{ $sentList->department_label }}
                    </span>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Creado: {{ $sentList->created_at->format('d/m/Y H:i') }}
                </span>
            </div>

            {{-- Period --}}
            <div class="mt-4 rounded-lg border-2 border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Período de planificación</p>
                <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                    Semana {{ $sentList->start_date->weekOfYear }} – {{ $sentList->start_date->year }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $sentList->start_date->format('d/m/Y') }} – {{ $sentList->end_date->format('d/m/Y') }}
                </p>
            </div>
        </div>

        {{-- Planning: resources + capacity --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recursos asignados</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Personas</dt>
                        <dd class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $sentList->num_persons }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Turnos</dt>
                        <dd class="mt-1 flex flex-wrap gap-1">
                            @foreach ($sentList->shifts as $shift)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ $shift->name }}
                                </span>
                            @endforeach
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="md:col-span-2 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resumen de capacidad</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="rounded-lg border-2 border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 text-center">
                        <p class="text-sm text-blue-700 dark:text-blue-300">Disponibles</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($sentList->total_available_hours, 2) }}</p>
                    </div>
                    <div class="rounded-lg border-2 border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 text-center">
                        <p class="text-sm text-amber-700 dark:text-amber-300">Usadas</p>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($sentList->used_hours, 2) }}</p>
                    </div>
                    <div class="rounded-lg border-2 p-4 text-center {{ $sentList->remaining_hours >= 0 ? 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20' : 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20' }}">
                        <p class="text-sm {{ $sentList->remaining_hours >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">Restantes</p>
                        <p class="text-2xl font-bold {{ $sentList->remaining_hours >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ number_format($sentList->remaining_hours, 2) }}
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <span>Utilización</span>
                        <span>{{ $sentList->capacity_utilization }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full transition-all duration-500
                            {{ $sentList->capacity_utilization < 80 ? 'bg-green-500' : '' }}
                            {{ $sentList->capacity_utilization >= 80 && $sentList->capacity_utilization < 100 ? 'bg-amber-500' : '' }}
                            {{ $sentList->capacity_utilization >= 100 ? 'bg-red-500' : '' }}"
                            style="width: {{ min($sentList->capacity_utilization, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Department workflow & POs --}}
        <livewire:admin.sent-lists.sent-list-department-view :sentList="$sentList" :key="'dept-view-'.$sentList->id" />
    </div>
</x-layouts.admin>
