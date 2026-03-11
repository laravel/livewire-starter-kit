{{-- Step 1: Disponibilidad de horas --}}
<div>
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Paso 1 de 4 — Disponibilidad de horas
    </h2>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Left: Form --}}
        <div class="space-y-4">
            {{-- Shift selection --}}
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Seleccionar turnos</label>
                <div class="space-y-2">
                    @foreach($shifts as $shift)
                        @php
                            $employeeCount = collect($loadedEmployees)->firstWhere('shift_id', $shift->id);
                            $count = $employeeCount ? count($employeeCount['employees']) : 0;
                        @endphp
                        <label class="flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition {{ in_array($shift->id, $selectedShifts) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-600' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                            <input
                                type="checkbox"
                                wire:model.live="selectedShifts"
                                value="{{ $shift->id }}"
                                class="rounded border-2 border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700"
                            >
                            <div class="flex-1 min-w-0">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $shift->name }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">
                                    ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                </span>
                            </div>
                            @if(in_array($shift->id, $selectedShifts))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $count > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' }}">
                                    {{ $count }} empleado(s)
                                </span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Date range --}}
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="startDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha inicio</label>
                        <input wire:model.live="startDate" id="startDate" type="date"
                            class="block w-full px-3 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label for="endDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha fin</label>
                        <input wire:model.live="endDate" id="endDate" type="date"
                            class="block w-full px-3 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Máximo 5 días de rango</p>
            </div>

            {{-- Employees loaded --}}
            @if(count($loadedEmployees) > 0)
                <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-medium text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Empleados cargados
                            <span class="ml-auto text-sm font-normal text-gray-500 dark:text-gray-400">Total: {{ $numPersons }}</span>
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-64 overflow-y-auto">
                        @foreach($loadedEmployees as $group)
                            <details class="group">
                                <summary class="flex items-center justify-between px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $group['shift_name'] }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ count($group['employees']) }}
                                        </span>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </summary>
                                <div class="px-4 pb-3 space-y-2">
                                    @foreach(array_slice($group['employees'], 0, 10) as $employee)
                                        <div class="flex items-center gap-3 text-sm py-1">
                                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-xs font-medium text-gray-600 dark:text-gray-300">
                                                {{ substr($employee['name'], 0, 1) }}{{ substr($employee['last_name'] ?? '', 0, 1) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900 dark:text-white truncate">{{ $employee['full_name'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $employee['employee_number'] ?? 'Sin número' }} • {{ $employee['position'] ?? 'Sin posición' }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(count($group['employees']) > 10)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-2">... y {{ count($group['employees']) - 10 }} más</p>
                                    @endif
                                </div>
                            </details>
                        @endforeach
                    </div>
                </div>
            @elseif(count($selectedShifts) > 0)
                <div class="rounded-lg border-2 border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-amber-800 dark:text-amber-200">Sin empleados asignados</p>
                            <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                No hay empleados activos asignados a los turnos seleccionados. El cálculo de horas será 0.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right: Summary --}}
        <div class="space-y-4">
            @if(count($shiftDetails) > 0)
                <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 dark:text-white mb-3">Detalle de turnos</h3>
                    <div class="space-y-2">
                        @foreach($shiftDetails as $detail)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">{{ $detail['name'] }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $detail['net_hours'] }} hrs/día</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 border-2 border-teal-200 dark:border-teal-800 rounded-lg p-4">
                <h3 class="font-medium text-teal-900 dark:text-teal-100 mb-2">Empleados asignados</h3>
                <p class="text-3xl font-bold text-teal-600 dark:text-teal-400">{{ $numPersons }}</p>
                <p class="text-sm text-teal-700 dark:text-teal-300 mt-1">en {{ count($selectedShifts) }} turno(s) seleccionado(s)</p>
            </div>

            <div class="bg-white dark:bg-gray-800 border-2 border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h3 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Total horas disponibles</h3>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalAvailableHours, 2) }} hrs</p>
                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">{{ count($selectedShifts) }} turno(s) × {{ $numPersons }} persona(s)</p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="flex justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button wire:click="nextStep" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors">
            Siguiente
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </div>
</div>
