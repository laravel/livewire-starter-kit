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
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
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
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Number of Persons --}}
            <div>
                <flux:input 
                    wire:model.live="numPersons" 
                    type="number" 
                    min="1" 
                    max="100"
                    label="Número de Personas"
                />
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
        <flux:button wire:click="nextStep" variant="primary">
            Siguiente
            <flux:icon.arrow-right class="w-4 h-4 ml-2" />
        </flux:button>
    </div>
</div>
