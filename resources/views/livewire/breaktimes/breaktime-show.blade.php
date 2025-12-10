<?php

use Livewire\Volt\Component;
use App\Models\BreakTime;

new class extends Component {
    public BreakTime $breakTime;

    public function mount(BreakTime $breakTime)
    {
        $this->breakTime = $breakTime->load('shift');
    }

    public function getDuration()
    {
        $start = \Carbon\Carbon::parse($this->breakTime->start_break_time);
        $end = \Carbon\Carbon::parse($this->breakTime->end_break_time);
        $duration = $start->diff($end);

        $hours = $duration->h;
        $minutes = $duration->i;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'min';
        }
        return $minutes . 'min';
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $breakTime->name }}</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Detalles del descanso
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <div class="flex space-x-2">
                        @if (Route::has('break-times.edit'))
                            <a href="{{ route('break-times.edit', $breakTime) }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Editar
                            </a>
                        @endif
                        @if (Route::has('break-times.index'))
                            <a href="{{ route('break-times.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Volver
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles del Descanso -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    {{ $breakTime->name }}
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Información detallada del descanso
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Nombre del Descanso
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            {{ $breakTime->name }}
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Turno
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                @if (Route::has('shifts.show') && $breakTime->shift)
                                    <a href="{{ route('shifts.show', $breakTime->shift) }}"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $breakTime->shift->name }}
                                    </a>
                                @else
                                    {{ $breakTime->shift->name ?? 'Sin turno asignado' }}
                                @endif
                            </div>
                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Horario
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ \Carbon\Carbon::parse($breakTime->start_break_time)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($breakTime->end_break_time)->format('H:i') }}
                            </div>
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Duración
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            {{ $this->getDuration() }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Estado
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            @if ($breakTime->active)
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Activo
                                </span>
                            @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Inactivo
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Comentarios
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            {{ $breakTime->comments ?? 'Sin comentarios' }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Fecha de Creación
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            {{ $breakTime->created_at->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                            Última Actualización
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            {{ $breakTime->updated_at->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Información del Turno Asociado -->
        @if ($breakTime->shift)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Información del Turno</h3>
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-base font-medium text-gray-900 dark:text-white">
                                    {{ $breakTime->shift->name }}
                                </h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Turno al que pertenece este descanso
                                </p>
                            </div>
                            @if (Route::has('shifts.show'))
                                <a href="{{ route('shifts.show', $breakTime->shift) }}"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Ver Turno
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Horario del Turno
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($breakTime->shift->start_time)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($breakTime->shift->end_time)->format('H:i') }}
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Estado del Turno
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    @if ($breakTime->shift->active)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Activo
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Inactivo
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Total de Descansos
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $breakTime->shift->BreakTimes->count() }} descanso(s)
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Resumen Visual -->
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Resumen Visual</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Hora de Inicio
                            </dt>
                            <dd class="mt-1 text-2xl font-semibold text-blue-600 dark:text-blue-400">
                                {{ \Carbon\Carbon::parse($breakTime->start_break_time)->format('H:i') }}
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Hora de Fin
                            </dt>
                            <dd class="mt-1 text-2xl font-semibold text-blue-600 dark:text-blue-400">
                                {{ \Carbon\Carbon::parse($breakTime->end_break_time)->format('H:i') }}
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Duración Total
                            </dt>
                            <dd class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">
                                {{ $this->getDuration() }}
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Estado
                            </dt>
                            <dd class="mt-1 text-lg font-semibold">
                                @if ($breakTime->active)
                                    <span class="text-green-600 dark:text-green-400">Activo</span>
                                @else
                                    <span class="text-red-600 dark:text-red-400">Inactivo</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
