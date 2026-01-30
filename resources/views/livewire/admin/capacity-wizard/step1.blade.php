{{-- Step 1: Disponibilidad de horas --}}
<div>
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Paso 1 de 3 - Disponibilidad de Horas
    </h2>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Left Column: Form --}}
        <div class="space-y-4">
            {{-- Shift Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Seleccionar Turnos
                </label>
                <div class="space-y-2">
                    @foreach($shifts as $shift)
                        @php
                            $employeeCount = collect($loadedEmployees)->firstWhere('shift_id', $shift->id);
                            $count = $employeeCount ? count($employeeCount['employees']) : 0;
                        @endphp
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer {{ in_array($shift->id, $selectedShifts) ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700' : '' }}">
                            <input 
                                type="checkbox" 
                                wire:model.live="selectedShifts" 
                                value="{{ $shift->id }}"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <div class="flex-1">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $shift->name }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">
                                    ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                </span>
                            </div>
                            @if(in_array($shift->id, $selectedShifts))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $count > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                    {{ $count }} empleado(s)
                                </span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Date Range --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input 
                        wire:model.live="startDate" 
                        type="date" 
                        label="Fecha Inicio"
                    />
                </div>
                <div>
                    <flux:input 
                        wire:model.live="endDate" 
                        type="date" 
                        label="Fecha Fin"
                    />
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                * Máximo 5 días de rango
            </p>

            {{-- Employees Loaded Section --}}
            @if(count($loadedEmployees) > 0)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-medium text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Empleados Cargados
                            <span class="ml-auto text-sm font-normal text-gray-500 dark:text-gray-400">
                                Total: {{ $numPersons }}
                            </span>
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-64 overflow-y-auto">
                        @foreach($loadedEmployees as $group)
                            <details class="group">
                                <summary class="flex items-center justify-between px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50">
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
                                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-2">
                                            ... y {{ count($group['employees']) - 10 }} más
                                        </p>
                                    @endif
                                </div>
                            </details>
                        @endforeach
                    </div>
                </div>
            @elseif(count($selectedShifts) > 0)
                {{-- No employees warning --}}
                <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-yellow-800 dark:text-yellow-200">Sin empleados asignados</p>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                No hay empleados activos asignados a los turnos seleccionados. 
                                El cálculo de horas será 0.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column: Summary --}}
        <div class="space-y-4">
            {{-- Shift Details --}}
            @if(count($shiftDetails) > 0)
                <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4">
                    <h3 class="font-medium text-gray-900 dark:text-white mb-3">Detalle de Turnos</h3>
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

            {{-- Total Employees Card --}}
            <div class="rounded-lg bg-purple-50 dark:bg-purple-900/20 p-4">
                <h3 class="font-medium text-purple-900 dark:text-purple-100 mb-2">Empleados Asignados</h3>
                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                    {{ $numPersons }}
                </p>
                <p class="text-sm text-purple-700 dark:text-purple-300 mt-1">
                    en {{ count($selectedShifts) }} turno(s) seleccionado(s)
                </p>
            </div>

            {{-- Total Available Hours --}}
            <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4">
                <h3 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Total Horas Disponibles</h3>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                    {{ number_format($totalAvailableHours, 2) }} hrs
                </p>
                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                    {{ count($selectedShifts) }} turno(s) × {{ $numPersons }} persona(s)
                </p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="flex justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <flux:button wire:click="nextStep" variant="primary" class="flex items-center gap-3">
            Siguiente
            <flux:icon.arrow-right class="w-4 h-4 ml-2" />
        </flux:button>
    </div>
</div>
