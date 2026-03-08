{{-- Step 4: Fechas Programadas de Envío --}}
<div>
    @php
        $weekNumber = \Carbon\Carbon::parse($startDate)->weekOfYear;
        $year = \Carbon\Carbon::parse($startDate)->year;
    @endphp
    
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Paso 4 de 4 - Fechas Programadas de Envío - Semana {{ $weekNumber }}-{{ $year }}
    </h2>

    @if($generatedSentListId)
        {{-- Success State --}}
        <div class="text-center py-8">
            <div class="flex justify-center mb-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900 border-2 border-green-200 dark:border-green-800">
                    <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">¡Lista Preliminar Generada Exitosamente!</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                La lista preliminar #{{ $generatedSentListId }} ha sido creada y enviada al departamento de Materiales.
                Ahora pasará por todos los departamentos: Materiales → Inspección → Producción → Envíos.
            </p>
            <div class="flex justify-center gap-4">
                <button wire:click="viewSentList" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Ver Lista
                </button>
                <button wire:click="resetWizard" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Nueva Calculación
                </button>
            </div>
        </div>
    @else
        {{-- Scheduled Ship Dates Form --}}
        <div class="space-y-6">
            {{-- Instructions --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-medium text-blue-800 dark:text-blue-200">Fecha Programada de Envío</p>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            Asigne una fecha programada de envío para toda la lista preliminar. 
                            Esta fecha se aplicará a todos los Work Orders de la lista.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Single Date Input --}}
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Fecha Programada de Envío para toda la lista
                </label>
                <input 
                    type="date" 
                    wire:model="scheduledShipDate"
                    class="w-full max-w-md rounded-md p-3 border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    min="{{ now()->format('Y-m-d') }}"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Esta fecha se aplicará a todos los {{ count($workOrderItems) }} Purchase Order(s) de la lista
                </p>
            </div>

            {{-- PO Summary Table --}}
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Resumen de Purchase Orders ({{ count($workOrderItems) }})
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">WO</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PO Number</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Número de Parte</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Horas Req.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lotes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($workOrderItems as $index => $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-blue-600 dark:text-blue-400">
                                        {{ $item['wo'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-blue-600 dark:text-blue-400">
                                        {{ $item['po_number'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ $item['part_number'] }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                        {{ number_format($item['quantity']) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                        {{ number_format($item['required_hours'], 2) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $lots = $lotNumbers[$index] ?? [];
                                        @endphp
                                        @if(!empty($lots) && is_array($lots))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($lots as $lot)
                                                    @if(is_array($lot) && !empty($lot['number']))
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                            {{ $lot['number'] }}
                                                            @if(!empty($lot['quantity']))
                                                                <span class="ml-1">({{ number_format($lot['quantity']) }})</span>
                                                            @endif
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-sm">Sin lotes</span>
                                        @endif
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

            {{-- Summary --}}
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border-2 border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 text-center">
                    <p class="text-sm text-blue-700 dark:text-blue-300">Horas Disponibles</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalAvailableHours, 2) }}</p>
                </div>
                <div class="rounded-lg border-2 border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 p-4 text-center">
                    <p class="text-sm text-orange-700 dark:text-orange-300">Horas Utilizadas</p>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($totalRequiredHours, 2) }}</p>
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
                    ])>Horas Restantes</p>
                    <p @class([
                        'text-3xl font-bold',
                        'text-green-600 dark:text-green-400' => $remainingHours >= 0,
                        'text-red-600 dark:text-red-400' => $remainingHours < 0,
                    ])>{{ number_format($remainingHours, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="flex justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button wire:click="previousStep" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Anterior
            </button>
            <div class="flex gap-3">
                <button wire:click="resetWizard" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Nueva Calculación
                </button>
                <button wire:click="generateSentList" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    Generar Lista Preliminar y Enviar a Materiales
                </button>
            </div>
        </div>
    @endif
</div>
