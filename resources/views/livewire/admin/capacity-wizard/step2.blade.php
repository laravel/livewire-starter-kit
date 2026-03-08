{{-- Step 2: Cálculo de horas necesarias por número de parte --}}
<div>
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Paso 2 de 4 — Cálculo de horas necesarias
    </h2>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Left Column: Add Part Form --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4">
                <h3 class="font-medium text-gray-900 dark:text-white mb-4">Agregar Número de Parte</h3>

                {{-- Button to open PO Modal --}}
                <button wire:click="openPOModal" type="button"
                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Cargar desde WOs
                </button>

                @if ($parts->isEmpty())
                    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-xs text-yellow-700 dark:text-yellow-300">
                            No hay partes con estándar activo. Debe crear un estándar primero.
                        </p>
                        <a href="{{ route('admin.standards.create') }}"
                            class="text-xs text-blue-600 hover:underline mt-1 inline-block">
                            Crear Estándar →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Work Orders List & Summary --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Hours Summary --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-lg border-2 border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 text-center">
                    <p class="text-sm text-blue-700 dark:text-blue-300">Disponibles</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($totalAvailableHours, 2) }}</p>
                </div>
                <div class="rounded-lg border-2 border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 p-4 text-center">
                    <p class="text-sm text-orange-700 dark:text-orange-300">Requeridas</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                        {{ number_format($totalRequiredHours, 2) }}</p>
                </div>
                <div @class([
                    'rounded-lg border-2 p-4 text-center',
                    'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20' => $remainingHours >= 0,
                    'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20' => $remainingHours < 0,
                ])>
                    <p @class([
                        'text-sm',
                        'text-green-700 dark:text-green-300' => $remainingHours >= 0,
                        'text-red-700 dark:text-red-300' => $remainingHours < 0,
                    ])>Diferencia</p>
                    <p @class([
                        'text-2xl font-bold',
                        'text-green-600 dark:text-green-400' => $remainingHours >= 0,
                        'text-red-600 dark:text-red-400' => $remainingHours < 0,
                    ])>{{ number_format($remainingHours, 2) }}</p>
                </div>
            </div>

            {{-- Overtime Suggestion --}}
            @if ($suggestedOvertime > 0)
                <div
                    class="rounded-lg border-2 border-yellow-200 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        <div>
                            <p class="font-medium text-yellow-800 dark:text-yellow-200">Sugerencia de Tiempo Extra</p>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                Se requieren <strong>{{ number_format($suggestedOvertime, 2) }} horas</strong>
                                adicionales.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Work Orders Table --}}
            <div class="rounded-lg border-2 border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                # Parte</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                WO</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Cantidad</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Tipo Estación</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Personas</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Unid/Hora</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Horas Req.</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($workOrderItems as $index => $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $item['part_number'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ Str::limit($item['part_description'] ?? '', 25) }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $item['wo'] ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ number_format($item['quantity']) }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $item['configuration']['workstation_type_label'] ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ $item['configuration']['persons_required'] ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">
                                    {{ number_format($item['configuration']['units_per_hour'] ?? 0) }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                    {{ number_format($item['required_hours'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="removeWorkOrderItem({{ $index }})" type="button"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No hay números de parte agregados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="flex justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button wire:click="previousStep" type="button"
            class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Anterior
        </button>
        <button wire:click="nextStep" type="button"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
            Siguiente
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>
</div>


{{-- PO Selection Modal --}}
@if ($showPOModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closePOModal"></div>

            {{-- Modal panel --}}
            <div
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Seleccionar Work Orders
                        </h3>
                        <button wire:click="closePOModal" class="text-gray-400 hover:text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="mb-4">
                        <input wire:model.live.debounce.300ms="poSearchTerm" type="text"
                            placeholder="Buscar por WO o número de parte..."
                            class="w-full rounded-md p-3 border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                    </div>

                    {{-- PO List --}}
                    <div class="max-h-96 overflow-y-auto">
                        @forelse($this->availablePOs as $po)
                            @php
                                $standard = $po->part->standards->where('active', true)->first();
                                $configurations = $standard ? $standard->configurations : collect();
                                $isSelected = in_array($po->id, $selectedPOs);
                            @endphp
                            <div @class([
                                'border rounded-lg p-4 mb-3',
                                'border-blue-500 bg-blue-50 dark:bg-blue-900/20' => $isSelected,
                                'border-gray-200 dark:border-gray-700' => !$isSelected,
                            ])>
                                <div class="flex items-start gap-3">
                                    {{-- Checkbox --}}
                                    <input type="checkbox" wire:click="togglePOSelection({{ $po->id }})"
                                        @checked($isSelected)
                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />

                                    <div class="flex-1">
                                        {{-- WO Info --}}
                                        <div class="flex items-center justify-between mb-2">
                                            <div>
                                                <span
                                                    class="font-semibold text-gray-900 dark:text-white">{{ $po->wo ?? '-' }}</span>
                                                <span class="mx-2 text-gray-400">|</span>
                                                <span
                                                    class="text-gray-700 dark:text-gray-300">{{ $po->part->number }}</span>
                                            </div>
                                            <span class="text-sm text-gray-500">Qty:
                                                {{ number_format($po->quantity) }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                            {{ $po->part->description }}</p>

                                        {{-- Configuration Selection --}}
                                        @if ($isSelected && $configurations->isNotEmpty())
                                            <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                    Seleccionar Configuración:
                                                </label>
                                                <div class="space-y-2">
                                                    @foreach ($configurations as $config)
                                                        @php
                                                            $canUse = $config->persons_required <= $numPersons;
                                                        @endphp
                                                        <label @class([
                                                            'flex items-center p-2 rounded cursor-pointer',
                                                            'hover:bg-gray-100 dark:hover:bg-gray-800' => $canUse,
                                                            'opacity-50 cursor-not-allowed' => !$canUse,
                                                        ])>
                                                            <input type="radio" name="config_{{ $po->id }}"
                                                                wire:click="setConfigurationForPO({{ $po->id }}, {{ $config->id }})"
                                                                @disabled(!$canUse)
                                                                @checked(isset($poConfigurations[$po->id]) && $poConfigurations[$po->id] == $config->id) class="mr-2" />
                                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                                {{ $config->workstation_type_label }} -
                                                                {{ $config->persons_required }} persona(s) -
                                                                {{ $config->units_per_hour }} uph
                                                                @if (!$canUse)
                                                                    <span class="text-red-500 ml-2">(Requiere más
                                                                        personas)</span>
                                                                @endif
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                                @if (!isset($poConfigurations[$po->id]))
                                                    <p class="text-xs text-gray-500 mt-2">
                                                        Si no selecciona, se usará la configuración óptima
                                                        automáticamente
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                No hay POs aprobados con configuraciones disponibles
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="addSelectedPOs" type="button"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Agregar Seleccionados ({{ count($selectedPOs) }})
                    </button>
                    <button wire:click="closePOModal" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
