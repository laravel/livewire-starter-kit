<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200" wire:poll.30s>
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-20 shadow-md">
        <div class="px-4 py-4">
            <div class="flex flex-col gap-4">
                {{-- Título y Logo --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('flexcon.png') }}" alt="Flexcon" class="h-10 sm:h-12 w-auto">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">LISTA DE ENVÍO</h1>
                    </div>
                    {{-- Indicador de actualización --}}
                    <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4 animate-spin" wire:loading xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="hidden sm:inline">Auto-actualización cada {{ $refreshInterval }}s</span>
                        <span class="sm:hidden">{{ $refreshInterval }}s</span>
                    </div>
                </div>
                
                {{-- Filtros --}}
                <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                    <div class="flex flex-col sm:flex-row gap-2 sm:items-center flex-1">
                        <label class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 sm:hidden">Filtros</label>
                        <select wire:model.live="filterDepartment" class="w-full sm:w-auto text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500">
                            <option value="">Todos los Departamentos</option>
                            @foreach(\App\Models\SentList::getDepartments() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="filterStatus" class="w-full sm:w-auto text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500">
                            <option value="">Todos los Estados</option>
                            @foreach(\App\Models\SentList::getStatuses() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <button 
                            wire:click="toggleCompleted" 
                            class="w-full sm:w-auto px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $showCompleted ? 'bg-green-500 dark:bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}"
                        >
                            {{ $showCompleted ? 'Ocultar' : 'Mostrar' }} Completados
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido Principal --}}
    <div class="px-4 sm:px-6 py-6 space-y-6 pb-20">
        @foreach($workOrdersGrouped as $workstationType => $workOrders)
            {{-- Sección por Tipo de Estación --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                {{-- Header de Sección --}}
                <div class="px-4 sm:px-6 py-4 {{ $this->getWorkstationHeaderColor($workstationType) }}">
                    <h2 class="text-lg sm:text-xl font-bold text-white">{{ $workstationType }}</h2>
                </div>

                {{-- Vista Desktop: Tabla --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">DOC</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">WO #</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Item #</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300"># Parte</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Descripción</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Cantidad WO</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Piezas Enviadas</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Cantidad Pendiente</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Cantidad a Enviar</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Fecha Prog. A</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Fecha de Envío</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Fecha de Apertura</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">EG</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">PR</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($workOrders as $wo)
                                @php
                                    $po = $wo->purchaseOrder;
                                    $part = $po->part;
                                    $allLots = $wo->lots; // Todos los lotes
                                    $completedLots = $wo->lots->where('status', \App\Models\Lot::STATUS_COMPLETED);
                                    $totalSent = $completedLots->sum('quantity');
                                    $pending = $wo->quantity - $wo->sent_pieces;
                                    $toSend = $completedLots->sum('quantity');
                                @endphp

                                {{-- Fila Principal de WO --}}
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">WO</td>
                                    <td class="px-4 py-3 font-medium text-blue-600 dark:text-blue-400">{{ $po->po_number }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $part->item_number }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100">{{ $part->number }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs truncate" title="{{ $part->description }}">{{ $part->description }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">{{ number_format($wo->quantity) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($wo->sent_pieces) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($pending) }}</td>
                                    <td class="px-4 py-3 text-right font-bold bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200">{{ number_format($toSend) }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $wo->scheduled_send_date?->format('m/d/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $wo->actual_send_date?->format('m/d/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $wo->created_at->format('m/d/Y') }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $wo->sentList?->id ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $wo->priority ?? '-' }}</td>
                                </tr>

                                {{-- Filas de TODOS los Lotes (con estado) --}}
                                @foreach($allLots as $lot)
                                    <tr class="bg-gray-50 dark:bg-gray-700/30 text-gray-600 dark:text-gray-400">
                                        <td class="px-4 py-2 pl-8 text-xs">Lote</td>
                                        <td class="px-4 py-2 text-xs">{{ $po->po_number }}.{{ $lot->lot_number }}</td>
                                        <td class="px-4 py-2 text-xs">{{ $part->item_number }}</td>
                                        <td class="px-4 py-2 text-xs font-medium">{{ $part->number }}</td>
                                        <td class="px-4 py-2 text-xs max-w-xs truncate" title="{{ $lot->description ?? $part->description }}">{{ $lot->description ?? $part->description }}</td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs font-medium">{{ number_format($lot->quantity) }}</td>
                                        <td class="px-4 py-2 text-center text-xs">{{ $wo->scheduled_send_date?->format('m/d/Y') ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center text-xs">{{ $wo->actual_send_date?->format('m/d/Y') ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center text-xs">{{ $wo->created_at->format('m/d/Y') }}</td>
                                        <td class="px-4 py-2 text-center text-xs">{{ $wo->sentList?->id ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center text-xs">
                                            @php
                                                $statusColors = [
                                                    \App\Models\Lot::STATUS_PENDING => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-800 dark:text-gray-200', 'label' => 'Pendiente'],
                                                    \App\Models\Lot::STATUS_IN_PROGRESS => ['bg' => 'bg-blue-100 dark:bg-blue-900/50', 'text' => 'text-blue-800 dark:text-blue-200', 'label' => 'En Proceso'],
                                                    \App\Models\Lot::STATUS_COMPLETED => ['bg' => 'bg-green-100 dark:bg-green-900/50', 'text' => 'text-green-800 dark:text-green-200', 'label' => 'Completado'],
                                                ];
                                                $statusInfo = $statusColors[$lot->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => $lot->status];
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusInfo['bg'] }} {{ $statusInfo['text'] }}">
                                                {{ $statusInfo['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Fila de Total si hay múltiples lots --}}
                                @if($allLots->count() > 1)
                                    <tr class="bg-blue-50 dark:bg-blue-900/30 font-semibold">
                                        <td colspan="8" class="px-4 py-2 text-right text-gray-900 dark:text-gray-100">Total:</td>
                                        <td class="px-4 py-2 text-right text-gray-900 dark:text-gray-100">{{ number_format($allLots->sum('quantity')) }}</td>
                                        <td colspan="5"></td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Vista Móvil/Tablet: Cards --}}
                <div class="lg:hidden divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($workOrders as $wo)
                        @php
                            $po = $wo->purchaseOrder;
                            $part = $po->part;
                            $completedLots = $wo->lots->where('status', \App\Models\Lot::STATUS_COMPLETED);
                            $totalSent = $completedLots->sum('quantity');
                            $pending = $wo->quantity - $wo->sent_pieces;
                            $toSend = $completedLots->sum('quantity');
                        @endphp

                        {{-- Card Principal WO --}}
                        <div class="p-4 space-y-3 bg-white dark:bg-gray-800">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">WO</span>
                                        <span class="text-base font-bold text-blue-600 dark:text-blue-400">{{ $po->po_number }}</span>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">{{ $part->item_number }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">{{ $part->description }}</div>
                                </div>
                            </div>

                            {{-- Grid de información --}}
                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cantidad WO</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($wo->quantity) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Piezas Enviadas</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($wo->sent_pieces) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cantidad Pendiente</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($pending) }}</div>
                                </div>
                                <div class="bg-yellow-100 dark:bg-yellow-900/50 rounded-lg p-2">
                                    <div class="text-xs text-yellow-800 dark:text-yellow-200 mb-1 font-medium">Cantidad a Enviar</div>
                                    <div class="text-sm font-bold text-yellow-900 dark:text-yellow-100">{{ number_format($toSend) }}</div>
                                </div>
                            </div>

                            {{-- Fechas --}}
                            <div class="grid grid-cols-3 gap-2 text-xs pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Prog. A</div>
                                    <div class="text-gray-900 dark:text-gray-100">{{ $wo->scheduled_send_date?->format('m/d/Y') ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Envío</div>
                                    <div class="text-gray-900 dark:text-gray-100">{{ $wo->actual_send_date?->format('m/d/Y') ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Apertura</div>
                                    <div class="text-gray-900 dark:text-gray-100">{{ $wo->created_at->format('m/d/Y') }}</div>
                                </div>
                            </div>

                            {{-- EG y PR --}}
                            <div class="flex items-center gap-4 text-xs pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">EG:</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium ml-1">{{ $wo->sentList?->id ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">PR:</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium ml-1">{{ $wo->priority ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Cards de TODOS los Lotes (con estado) --}}
                        @foreach($allLots as $lot)
                            @php
                                $statusColors = [
                                    \App\Models\Lot::STATUS_PENDING => ['border' => 'border-gray-400', 'bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-800 dark:text-gray-200', 'label' => 'Pendiente'],
                                    \App\Models\Lot::STATUS_IN_PROGRESS => ['border' => 'border-blue-500 dark:border-blue-400', 'bg' => 'bg-blue-100 dark:bg-blue-900/50', 'text' => 'text-blue-800 dark:text-blue-200', 'label' => 'En Proceso'],
                                    \App\Models\Lot::STATUS_COMPLETED => ['border' => 'border-green-500 dark:border-green-400', 'bg' => 'bg-green-100 dark:bg-green-900/50', 'text' => 'text-green-800 dark:text-green-200', 'label' => 'Completado'],
                                ];
                                $statusInfo = $statusColors[$lot->status] ?? ['border' => 'border-gray-400', 'bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => $lot->status];
                            @endphp
                            <div class="p-4 pl-8 bg-gray-50 dark:bg-gray-700/30 space-y-2 border-l-4 {{ $statusInfo['border'] }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lote</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $po->po_number }}.{{ $lot->lot_number }}</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusInfo['bg'] }} {{ $statusInfo['text'] }}">
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">{{ $lot->description ?? $part->description }}</div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Cantidad:</span>
                                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ number_format($lot->quantity) }}</span>
                                </div>
                            </div>
                        @endforeach

                        {{-- Total si hay múltiples lots --}}
                        @if($allLots->count() > 1)
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/30">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Total:</span>
                                    <span class="text-base font-bold text-blue-900 dark:text-blue-100">{{ number_format($allLots->sum('quantity')) }}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Mensaje si no hay datos --}}
        @if($workOrdersGrouped->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-gray-100">No hay lotes completados</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Los lotes completados aparecerán aquí automáticamente.</p>
            </div>
        @endif
    </div>

    {{-- Footer con información --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border-t border-gray-200 dark:border-gray-700 shadow-lg z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Última actualización: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ now()->format('d/m/Y H:i:s') }}</span></span>
                </div>
                <div class="flex items-center gap-4 sm:gap-6 text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrdersGrouped->flatten()->count() }}</span>
                        <span class="text-xs">WOs</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrdersGrouped->flatten()->sum(fn($wo) => $wo->lots->count()) }}</span>
                        <span class="text-xs">Lotes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    // Auto-scroll suave cuando hay nuevos datos
    $wire.on('refresh-display', () => {
        console.log('Display refreshed');
    });

    // Notificación sonora cuando se completa un lote (opcional)
    $wire.on('lotCompleted', () => {
        // Puedes agregar un sonido de notificación aquí
        console.log('New lot completed!');
    });
</script>
@endscript
