{{-- Step 3: Cierre y salida --}}
<div>
    @php
        $weekNumber = \Carbon\Carbon::parse($startDate)->weekOfYear;
        $year = \Carbon\Carbon::parse($startDate)->year;
    @endphp
    
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Paso 3 de 3 - Cierre - Período Semana {{ $weekNumber }}-{{ $year }}
    </h2>

    @if($generatedSentListId)
        {{-- Success State --}}
        <div class="text-center py-8">
            <div class="flex justify-center mb-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <flux:icon.check-circle class="h-10 w-10 text-green-600 dark:text-green-400" />
                </div>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">¡Lista Generada Exitosamente!</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                La lista de planificación #{{ $generatedSentListId }} ha sido creada para el departamento de Materiales.
            </p>
            <div class="flex justify-center gap-4">
                <flux:button wire:click="viewSentList" variant="primary">
                    <flux:icon.eye class="w-4 h-4 mr-2" />
                    Ver Lista
                </flux:button>
                <flux:button wire:click="resetWizard" variant="ghost">
                    <flux:icon.arrow-path class="w-4 h-4 mr-2" />
                    Nueva Calculación
                </flux:button>
            </div>
        </div>
    @else
        {{-- Summary View --}}
        <div class="space-y-6">
            {{-- Configuration Summary --}}
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Turnos Seleccionados</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ count($selectedShifts) }} turno(s)</p>
                </div>
                <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Personal</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $numPersons }} persona(s)</p>
                </div>
                <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Período</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        Semana {{ \Carbon\Carbon::parse($startDate)->weekOfYear }} - {{ \Carbon\Carbon::parse($startDate)->year }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            {{-- Hours Summary --}}
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 text-center">
                    <p class="text-sm text-blue-700 dark:text-blue-300">Horas Disponibles</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalAvailableHours, 2) }}</p>
                </div>
                <div class="rounded-lg bg-orange-50 dark:bg-orange-900/20 p-4 text-center">
                    <p class="text-sm text-orange-700 dark:text-orange-300">Horas Utilizadas</p>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($totalRequiredHours, 2) }}</p>
                </div>
                <div @class([
                    'rounded-lg p-4 text-center',
                    'bg-green-50 dark:bg-green-900/20' => $remainingHours >= 0,
                    'bg-red-50 dark:bg-red-900/20' => $remainingHours < 0,
                ])>
                    <p @class([
                        'text-sm',
                        'text-green-700 dark:text-green-300' => $remainingHours >= 0,
                        'text-red-700 dark:text-red-300' => $remainingHours < 0,
                    ])>Horas Restantes</p>
                    <p @class([
                        'text-3xl font-bold',
                        'text-green-600 dark:text-green-400' => $remainingHours >= 0,
                        'text-red-600 dark:text-red-400' => $remainingHours < 0,
                    ])>{{ number_format($remainingHours, 2) }}</p>
                </div>
            </div>

            {{-- Work Orders Summary Table --}}
            <div>
                <h3 class="font-medium text-gray-900 dark:text-white mb-3">Resumen de Números de Parte</h3>
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Número de Parte</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Horas Req.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($workOrderItems as $index => $item)
                                <tr>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $item['part_number'] }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ Str::limit($item['part_description'] ?? '', 40) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($item['quantity']) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ number_format($item['required_hours'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Total:</td>
                                <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">{{ number_format($totalRequiredHours, 2) }} hrs</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Overtime Warning --}}
            @if($suggestedOvertime > 0)
                <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-4">
                    <div class="flex items-center gap-3">
                        <flux:icon.exclamation-triangle class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                        <div>
                            <p class="font-medium text-yellow-800 dark:text-yellow-200">Atención: Capacidad Excedida</p>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                Se requieren {{ number_format($suggestedOvertime, 2) }} horas adicionales de tiempo extra.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Navigation --}}
        <div class="flex justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <flux:button wire:click="previousStep" variant="ghost">
                <flux:icon.arrow-left class="w-4 h-4 mr-2" />
                Anterior
            </flux:button>
            <div class="flex gap-3">
                <flux:button wire:click="resetWizard" variant="ghost">
                    <flux:icon.arrow-path class="w-4 h-4 mr-2" />
                    Nueva Calculación
                </flux:button>
                <flux:button wire:click="generateSentList" variant="primary">
                    <flux:icon.paper-airplane class="w-4 h-4 mr-2" />
                    Generar Lista para Materiales
                </flux:button>
            </div>
        </div>
    @endif
</div>
