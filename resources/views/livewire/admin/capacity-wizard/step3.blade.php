{{-- Step 3: Cierre y salida --}}
<div>
    @php
        $weekNumber = \Carbon\Carbon::parse($startDate)->weekOfYear;
        $year = \Carbon\Carbon::parse($startDate)->year;
    @endphp
    
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Paso 3 de 4 - Gestión de Lotes/Viajeros
    </h2>

    @if($generatedSentListId)
        {{-- Success State --}}
        <div class="text-center py-8">
            <div class="flex justify-center mb-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <flux:icon.check-circle class="h-10 w-10 text-green-600 dark:text-green-400" />
                </div>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">¡Lista Preliminar Generada Exitosamente!</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                La lista preliminar #{{ $generatedSentListId }} ha sido creada y enviada al departamento de Materiales.
                Ahora pasará por todos los departamentos: Materiales → Producción → Calidad → Envíos.
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
                <h3 class="font-medium text-gray-900 dark:text-white mb-3">Resumen de Purchase Orders en la Lista Preliminar</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Esta lista pasará por los departamentos: <strong>Materiales → Producción → Calidad → Envíos</strong>. 
                    Opcionalmente puede asignar números de lote/viajero a cada PO.
                </p>
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PO Number</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">WO</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Número de Parte</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Horas Req.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lote/Viajero</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($workOrderItems as $index => $item)
                                <tr>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-blue-600 dark:text-blue-400">{{ $item['po_number'] ?? '-' }}</td>
                                    <td class="px-4 py-3 font-medium text-indigo-600 dark:text-indigo-400">{{ $item['wo'] ?? '-' }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $item['part_number'] }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ Str::limit($item['part_description'] ?? '', 30) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($item['quantity']) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ number_format($item['required_hours'], 2) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            @php
                                                $lots = $lotNumbers[$index] ?? [];
                                                $lotCount = is_array($lots) ? count($lots) : 0;
                                            @endphp
                                            
                                            @if($lotCount > 0)
                                                <div class="flex flex-wrap gap-1 flex-1">
                                                    @foreach($lots as $lot)
                                                        @if(is_array($lot) && !empty($lot['number']))
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                                {{ $lot['number'] }}
                                                                @if(!empty($lot['quantity']))
                                                                    <span class="ml-1 text-blue-600 dark:text-blue-300">({{ number_format($lot['quantity']) }})</span>
                                                                @endif
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-sm flex-1">Sin lotes</span>
                                            @endif
                                            
                                            <button 
                                                wire:click="openLotModal({{ $index }})"
                                                type="button"
                                                class="inline-flex items-center justify-center p-1.5 rounded-md text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition"
                                                title="Gestionar lotes"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Total:</td>
                                <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">{{ number_format($totalRequiredHours, 2) }} hrs</td>
                                <td></td>
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
            <flux:button wire:click="nextStep" variant="primary">
                Siguiente - Fechas de Envío
                <flux:icon.arrow-right class="w-4 h-4 ml-2" />
            </flux:button>
        </div>
    @endif

    {{-- Modal para Agregar Múltiples Lotes --}}
    @if($showLotModal && $currentLotIndex !== null)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="lot-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeLotModal"></div>

                {{-- Modal panel --}}
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="lot-modal-title">
                                    Gestionar Lotes/Viajeros
                                </h3>
                                @if(isset($workOrderItems[$currentLotIndex]))
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        PO: <strong>{{ $workOrderItems[$currentLotIndex]['po_number'] ?? 'N/A' }}</strong> | 
                                        Parte: <strong>{{ $workOrderItems[$currentLotIndex]['part_number'] }}</strong>
                                    </p>
                                @endif
                            </div>
                            <button wire:click="closeLotModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        {{-- Lots List --}}
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            @foreach($tempLots as $lotIndex => $lot)
                                <div class="flex items-start gap-2">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-8 pt-2">
                                        {{ $lotIndex + 1 }}.
                                    </span>
                                    <div class="flex-1 space-y-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                No. Lote/Viajero
                                            </label>
                                            <input 
                                                type="text" 
                                                wire:model="tempLots.{{ $lotIndex }}.number"
                                                placeholder="Ej: 30"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Cantidad
                                            </label>
                                            <input 
                                                type="number" 
                                                wire:model="tempLots.{{ $lotIndex }}.quantity"
                                                placeholder="Ej: 400"
                                                min="1"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500"
                                            />
                                        </div>
                                    </div>
                                    @if(count($tempLots) > 1)
                                        <button 
                                            wire:click="removeLotInput({{ $lotIndex }})"
                                            type="button"
                                            class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition mt-6"
                                            title="Eliminar lote"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Add More Button --}}
                        <button 
                            wire:click="addLotInput"
                            type="button"
                            class="mt-4 w-full inline-flex items-center justify-center px-4 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:border-blue-500 hover:text-blue-500 transition"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Agregar otro lote
                        </button>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button 
                            wire:click="saveLots"
                            type="button"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Guardar Lotes
                        </button>
                        <button 
                            wire:click="closeLotModal"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
