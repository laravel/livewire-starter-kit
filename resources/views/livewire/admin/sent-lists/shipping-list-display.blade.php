<div class="min-h-screen bg-gray-50 dark:bg-gray-900" wire:poll.30s>
    {{-- Mensajes Flash --}}
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white px-4 py-3 rounded shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white px-4 py-3 rounded shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-20">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white">LISTA DE ENVÍO</h1>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4" wire:loading class="animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span class="hidden sm:inline">Actualización cada {{ $refreshInterval }}s</span>
                        <span class="sm:hidden">{{ $refreshInterval }}s</span>
                    </div>
                </div>
                
                {{-- Filtros --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <select wire:model.live="filterDepartment" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los Departamentos</option>
                        @foreach(\App\Models\SentList::getDepartments() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filterStatus" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los Estados</option>
                        @foreach(\App\Models\SentList::getStatuses() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <button 
                        wire:click="toggleCompleted" 
                        class="px-4 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                    >
                        {{ $showCompleted ? 'Ocultar' : 'Mostrar' }} Completados
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido Principal --}}
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 pb-20">
        @foreach($workOrdersGrouped as $workstationType => $workOrders)
            {{-- Sección por Tipo de Estación --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                {{-- Header de Sección --}}
                <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-blue-800 dark:bg-gray-900/50">
                    <h2 class="text-white font-semibold text-gray-900 ">{{ $workstationType }}</h2>
                </div>

                {{-- Vista Desktop: Tabla --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">DOC</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">WO #</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Item #</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider"># Parte</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Mat.</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Cal.</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Prod.</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Cant. WO</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Pz Enviadas</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Cant. Pendiente</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Cant. a Enviar</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha Prog. A</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha de Envío</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha de Apertura</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">EG</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($workOrders as $wo)
                                @php
                                    $po = $wo->purchaseOrder;
                                    $part = $po->part;
                                    $allLots = $wo->lots;
                                    $completedLots = $wo->lots->where('status', \App\Models\Lot::STATUS_COMPLETED);
                                    $totalSent = $completedLots->sum('quantity');
                                    $cantWO = $wo->original_quantity; // Cantidad total del WO
                                    $pzEnviadas = $wo->sent_pieces; // Piezas enviadas
                                    $cantAEnviar = $cantWO - $pzEnviadas; // Cant. a Enviar = Cant. WO - Pz Enviadas
                                    $toSend = $cantAEnviar;
                                    
                                    // Obtener estados de departamentos (simulado por ahora)
                                    $departmentStatuses = [
                                        'materials' => 'pending',
                                        'quality' => 'pending',
                                        'production' => 'pending',
                                    ];
                                @endphp

                                {{-- Fila Principal de WO --}}
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">WO</td>
                                    <td class="px-4 py-3 text-blue-600 dark:text-blue-400 font-medium">{{ $po->wo }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $part->item_number }}</td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white font-semibold">{{ $part->number }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs truncate" title="{{ $part->description }}">{{ $part->description }}</td>
                                    {{-- Semáforos de departamentos --}}
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $materialsColor = match($departmentStatuses['materials']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <button wire:click="openDepartmentStatusModal({{ $wo->id }}, 'materials')" class="w-6 h-6 rounded {{ $materialsColor }} hover:opacity-80 transition-opacity" title="Materiales"></button>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $qualityColor = match($departmentStatuses['quality']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <button wire:click="openDepartmentStatusModal({{ $wo->id }}, 'quality')" class="w-6 h-6 rounded {{ $qualityColor }} hover:opacity-80 transition-opacity" title="Calidad"></button>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $productionColor = match($departmentStatuses['production']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <button wire:click="openDepartmentStatusModal({{ $wo->id }}, 'production')" class="w-6 h-6 rounded {{ $productionColor }} hover:opacity-80 transition-opacity" title="Producción"></button>
                                    </td>
                                    {{-- Cantidades --}}
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-medium">{{ number_format($cantWO) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($pzEnviadas) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($cantAEnviar) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold bg-yellow-50 dark:bg-yellow-900/20 text-yellow-900 dark:text-yellow-200">{{ number_format($toSend) }}</td>
                                    {{-- Fechas --}}
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $wo->scheduled_send_date?->format('m/d/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $wo->actual_send_date?->format('m/d/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $wo->created_at->format('m/d/Y') }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $wo->sentList?->id ?? '-' }}</td>
                                </tr>

                                {{-- Filas de Lotes --}}
                                @foreach($allLots as $lot)
                                    @php
                                        $lotStatusInfo = match($lot->status) {
                                            \App\Models\Lot::STATUS_PENDING => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => 'Pendiente'],
                                            \App\Models\Lot::STATUS_IN_PROGRESS => ['bg' => 'bg-blue-50 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300', 'label' => 'En Proceso'],
                                            \App\Models\Lot::STATUS_COMPLETED => ['bg' => 'bg-green-50 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-300', 'label' => 'Completado'],
                                            default => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => $lot->status],
                                        };

                                        // Verificar si el lote puede ser inspeccionado por calidad
                                        $canInspectQuality = $lot->canBeInspectedByQuality();
                                        $qualityStatus = $lot->quality_status ?? 'pending';

                                        // Color del semaforo de calidad
                                        $lotQualityColor = match($qualityStatus) {
                                            'rejected' => 'bg-red-500',
                                            'pending' => 'bg-yellow-400',
                                            'approved' => 'bg-green-500',
                                            default => 'bg-gray-400',
                                        };

                                        // Si no puede ser inspeccionado, mostrar gris
                                        if (!$canInspectQuality) {
                                            $lotQualityColor = 'bg-gray-300 dark:bg-gray-600';
                                        }

                                        // Obtener razon de bloqueo si existe
                                        $qualityBlockedReason = $lot->getQualityBlockedReason();
                                    @endphp
                                    <tr class="bg-gray-50 dark:bg-gray-700/20">
                                        <td class="px-4 py-2 pl-8 text-xs text-gray-600 dark:text-gray-400">Lote</td>
                                        <td class="px-4 py-2 text-xs">
                                            <button wire:click="openLotModal({{ $wo->id }})" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                                {{ $lot->lot_number }}
                                            </button>
                                        </td>
                                        <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300">{{ $part->item_number }}</td>
                                        <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300 font-medium">{{ $part->number }}</td>
                                        <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300 max-w-xs truncate" title="{{ $lot->description ?? $part->description }}">{{ $lot->description ?? $part->description }}</td>
                                        {{-- Semaforo MAT - Estado del lote de produccion --}}
                                        <td class="px-4 py-2 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded {{ $lotStatusInfo['bg'] }} {{ $lotStatusInfo['text'] }}">
                                                {{ $lotStatusInfo['label'] }}
                                            </span>
                                        </td>
                                        {{-- Semaforo CAL - Status de Calidad por Lote --}}
                                        <td class="px-4 py-2 text-center">
                                            <button
                                                wire:click="openQualityModal({{ $lot->id }})"
                                                class="w-5 h-5 rounded {{ $lotQualityColor }} {{ $canInspectQuality ? 'hover:opacity-80 cursor-pointer' : 'cursor-not-allowed opacity-60' }} transition-opacity relative inline-flex items-center justify-center"
                                                title="{{ $canInspectQuality ? 'Status de Calidad: ' . ucfirst($qualityStatus) : ($qualityBlockedReason ?? 'Bloqueado') }}"
                                            >
                                                @if(!$canInspectQuality)
                                                    {{-- Icono de candado para indicar que esta bloqueado --}}
                                                    <svg class="w-3 h-3 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        </td>
                                        {{-- Columna Prod vacia por ahora --}}
                                        <td class="px-4 py-2 text-center text-xs text-gray-500 dark:text-gray-400">-</td>
                                        {{-- Cantidades --}}
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs font-medium text-gray-900 dark:text-white">{{ number_format($lot->quantity) }}</td>
                                        {{-- Fechas --}}
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400">{{ $wo->scheduled_send_date?->format('m/d/Y') ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400">{{ $wo->actual_send_date?->format('m/d/Y') ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400">{{ $wo->created_at->format('m/d/Y') }}</td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400">-</td>
                                    </tr>
                                @endforeach

                                {{-- Fila de Total --}}
                                @if($allLots->count() > 1)
                                    <tr class="bg-gray-100 dark:bg-gray-700/40 font-semibold">
                                        <td colspan="11" class="px-4 py-2 text-right text-gray-900 dark:text-white">Total:</td>
                                        <td class="px-4 py-2 text-right text-gray-900 dark:text-white">{{ number_format($allLots->sum('quantity')) }}</td>
                                        <td colspan="4"></td>
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
                            $allLots = $wo->lots;
                            $completedLots = $wo->lots->where('status', \App\Models\Lot::STATUS_COMPLETED);
                            $cantWO = $wo->original_quantity; // Cantidad total del WO
                            $pzEnviadas = $wo->sent_pieces; // Piezas enviadas
                            $cantAEnviar = $cantWO - $pzEnviadas; // Cant. a Enviar = Cant. WO - Pz Enviadas
                            $toSend = $cantAEnviar;
                            
                            $departmentStatuses = [
                                'materials' => 'pending',
                                'quality' => 'pending',
                                'production' => 'pending',
                            ];
                        @endphp

                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">WO</span>
                                        <span class="text-base font-semibold text-blue-600 dark:text-blue-400">{{ $po->wo }}</span>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white mb-1">{{ $part->item_number }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">{{ $part->description }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cant. WO</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($cantWO) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pz Enviadas</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($pzEnviadas) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cant. Pendiente</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($cantAEnviar) }}</div>
                                </div>
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-2">
                                    <div class="text-xs text-yellow-700 dark:text-yellow-300 mb-1 font-medium">Cant. a Enviar</div>
                                    <div class="text-sm font-semibold text-yellow-900 dark:text-yellow-200">{{ number_format($toSend) }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-xs pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Prog. A</div>
                                    <div class="text-gray-900 dark:text-white">{{ $wo->scheduled_send_date?->format('m/d/Y') ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Envío</div>
                                    <div class="text-gray-900 dark:text-white">{{ $wo->actual_send_date?->format('m/d/Y') ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Apertura</div>
                                    <div class="text-gray-900 dark:text-white">{{ $wo->created_at->format('m/d/Y') }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 text-xs pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">EG:</span>
                                    <span class="text-gray-900 dark:text-white font-medium ml-1">{{ $wo->sentList?->id ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">PR:</span>
                                    <span class="text-gray-900 dark:text-white font-medium ml-1">{{ $wo->priority ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Estado por Departamento</div>
                                <div class="flex items-center gap-4">
                                    <div class="flex flex-col items-center gap-1">
                                        @php
                                            $materialsColor = match($departmentStatuses['materials']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <button wire:click="openDepartmentStatusModal({{ $wo->id }}, 'materials')" class="w-8 h-8 rounded {{ $materialsColor }} hover:opacity-80 transition-opacity" title="Materiales"></button>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Mat.</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        @php
                                            $qualityColor = match($departmentStatuses['quality']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <button wire:click="openDepartmentStatusModal({{ $wo->id }}, 'quality')" class="w-8 h-8 rounded {{ $qualityColor }} hover:opacity-80 transition-opacity" title="Calidad"></button>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Cal.</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        @php
                                            $productionColor = match($departmentStatuses['production']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <button wire:click="openDepartmentStatusModal({{ $wo->id }}, 'production')" class="w-8 h-8 rounded {{ $productionColor }} hover:opacity-80 transition-opacity" title="Producción"></button>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Prod.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach($allLots as $lot)
                            @php
                                $statusInfo = match($lot->status) {
                                    \App\Models\Lot::STATUS_PENDING => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => 'Pendiente'],
                                    \App\Models\Lot::STATUS_IN_PROGRESS => ['bg' => 'bg-blue-50 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300', 'label' => 'En Proceso'],
                                    \App\Models\Lot::STATUS_COMPLETED => ['bg' => 'bg-green-50 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-300', 'label' => 'Completado'],
                                    default => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => $lot->status],
                                };

                                // Verificar si el lote puede ser inspeccionado por calidad
                                $canInspectQualityMobile = $lot->canBeInspectedByQuality();
                                $qualityStatusMobile = $lot->quality_status ?? 'pending';

                                // Color del semaforo de calidad
                                $lotQualityColorMobile = match($qualityStatusMobile) {
                                    'rejected' => 'bg-red-500',
                                    'pending' => 'bg-yellow-400',
                                    'approved' => 'bg-green-500',
                                    default => 'bg-gray-400',
                                };

                                // Si no puede ser inspeccionado, mostrar gris
                                if (!$canInspectQualityMobile) {
                                    $lotQualityColorMobile = 'bg-gray-300 dark:bg-gray-600';
                                }

                                // Obtener razon de bloqueo si existe
                                $qualityBlockedReasonMobile = $lot->getQualityBlockedReason();
                            @endphp
                            <div class="p-4 pl-8 bg-gray-50 dark:bg-gray-700/20 space-y-2 border-l-2 border-gray-300 dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <button wire:click="openLotModal({{ $wo->id }})" class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lote</span>
                                        <span>{{ $lot->lot_number }}</span>
                                    </button>
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded {{ $statusInfo['bg'] }} {{ $statusInfo['text'] }}">
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">{{ $lot->description ?? $part->description }}</div>

                                {{-- Semaforo de Calidad para movil --}}
                                <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Calidad:</span>
                                        <button
                                            wire:click="openQualityModal({{ $lot->id }})"
                                            class="w-6 h-6 rounded {{ $lotQualityColorMobile }} {{ $canInspectQualityMobile ? 'hover:opacity-80 cursor-pointer' : 'cursor-not-allowed opacity-60' }} transition-opacity relative inline-flex items-center justify-center"
                                            title="{{ $canInspectQualityMobile ? 'Status de Calidad: ' . ucfirst($qualityStatusMobile) : ($qualityBlockedReasonMobile ?? 'Bloqueado') }}"
                                        >
                                            @if(!$canInspectQualityMobile)
                                                <svg class="w-3 h-3 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </button>
                                        @if($canInspectQualityMobile)
                                            <span class="text-xs {{ $qualityStatusMobile === 'approved' ? 'text-green-600 dark:text-green-400' : ($qualityStatusMobile === 'rejected' ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400') }}">
                                                {{ ucfirst($qualityStatusMobile === 'pending' ? 'Pendiente' : ($qualityStatusMobile === 'approved' ? 'Aprobado' : 'No Aprobado')) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Bloqueado</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Cantidad:</span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($lot->quantity) }}</span>
                                </div>
                            </div>
                        @endforeach

                        @if($allLots->count() > 1)
                            <div class="p-4 bg-gray-100 dark:bg-gray-700/40">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Total:</span>
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">{{ number_format($allLots->sum('quantity')) }}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        @if($workOrdersGrouped->isEmpty())
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">No hay lotes</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Los lotes aparecerán aquí automáticamente.</p>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Última actualización: <span class="font-medium text-gray-900 dark:text-white">{{ now()->format('d/m/Y H:i:s') }}</span></span>
                </div>
                <div class="flex items-center gap-4 sm:gap-6 text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $workOrdersGrouped->flatten()->count() }}</span>
                        <span>WOs</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $workOrdersGrouped->flatten()->sum(fn($wo) => $wo->lots->count()) }}</span>
                        <span>Lotes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Gestión de Lotes --}}
    @if($showLotModal && $selectedWorkOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeLotModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Gestión de Lotes</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    WO: {{ $selectedWorkOrder->purchaseOrder->wo }} | Parte: {{ $selectedWorkOrder->purchaseOrder->part->number }}
                                </p>
                            </div>
                            <button wire:click="closeLotModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                        @if(count($lots) > 0)
                            <div class="space-y-3">
                                @foreach($lots as $index => $lot)
                                    <div class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-700">
                                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    No. Lote/Viajero
                                                </label>
                                                <input 
                                                    type="text" 
                                                    wire:model="lots.{{ $index }}.number"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="Ej: 001"
                                                >
                                                @error("lots.{$index}.number")
                                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Cantidad
                                                </label>
                                                <input 
                                                    type="number" 
                                                    wire:model="lots.{{ $index }}.quantity"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="Ej: 100"
                                                    min="1"
                                                >
                                                @error("lots.{$index}.quantity")
                                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <button 
                                            wire:click="removeLot({{ $index }})"
                                            class="flex-shrink-0 p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                            title="Eliminar lote"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-sm">No hay lotes. Agrega uno nuevo.</p>
                            </div>
                        @endif

                        <div class="mt-4">
                            <button 
                                wire:click="addLot"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Agregar Lote
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button 
                            wire:click="closeLotModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            wire:click="saveLots"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors"
                        >
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Estado de Departamentos --}}
    @if($showDepartmentStatusModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeDepartmentStatusModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Estado de Departamentos</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Actualizar estado por departamento</p>
                            </div>
                            <button wire:click="closeDepartmentStatusModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        @foreach(['materials' => 'Materiales', 'quality' => 'Calidad', 'production' => 'Producción'] as $deptKey => $deptLabel)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ $deptLabel }}
                                </label>
                                <div class="grid grid-cols-4 gap-2">
                                    <button
                                        wire:click="updateDepartmentStatus('{{ $deptKey }}', 'rejected')"
                                        class="px-3 py-2 text-xs font-medium border transition-colors {{ $departmentStatuses[$deptKey] === 'rejected' ? 'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400' }}"
                                    >
                                        Rechazado
                                    </button>
                                    <button
                                        wire:click="updateDepartmentStatus('{{ $deptKey }}', 'pending')"
                                        class="px-3 py-2 text-xs font-medium border transition-colors {{ $departmentStatuses[$deptKey] === 'pending' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400' }}"
                                    >
                                        Pendiente
                                    </button>
                                    <button
                                        wire:click="updateDepartmentStatus('{{ $deptKey }}', 'in_progress')"
                                        class="px-3 py-2 text-xs font-medium border transition-colors {{ $departmentStatuses[$deptKey] === 'in_progress' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400' }}"
                                    >
                                        En Progreso
                                    </button>
                                    <button
                                        wire:click="updateDepartmentStatus('{{ $deptKey }}', 'approved')"
                                        class="px-3 py-2 text-xs font-medium border transition-colors {{ $departmentStatuses[$deptKey] === 'approved' ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400' }}"
                                    >
                                        Aprobado
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button
                            wire:click="closeDepartmentStatusModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            wire:click="saveDepartmentStatuses"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors"
                        >
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Status de Calidad por Lote --}}
    @if($showQualityModal && $selectedLot)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="quality-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeQualityModal"></div>

                {{-- Modal Container --}}
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="quality-modal-title" class="text-lg font-semibold text-gray-900 dark:text-white">Status de Calidad - Lote</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    WO: {{ $selectedLot->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLot->lot_number }}
                                </p>
                            </div>
                            <button wire:click="closeQualityModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-6">
                        {{-- Informacion del Lote --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Informacion del Lote</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Parte:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                        {{ $selectedLot->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Cantidad:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                        {{ number_format($selectedLot->quantity) }} piezas
                                    </span>
                                </div>
                                @php
                                    $releasedKit = $selectedLot->getReleasedKit();
                                @endphp
                                @if($releasedKit)
                                <div class="col-span-2">
                                    <span class="text-gray-500 dark:text-gray-400">Kit:</span>
                                    <span class="ml-2 text-green-600 dark:text-green-400 font-medium">
                                        {{ $releasedKit->kit_number }} (Liberado)
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Status de Materiales (Solo lectura) --}}
                        <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg border border-green-200 dark:border-green-800">
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full bg-green-500 mr-3"></div>
                                <span class="text-sm font-medium text-green-800 dark:text-green-200">
                                    MAT. Liberado - Habilitado para inspeccion de calidad
                                </span>
                            </div>
                        </div>

                        {{-- Status de Calidad --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Status de Calidad
                            </label>
                            <div class="grid grid-cols-3 gap-3">
                                {{-- Pendiente --}}
                                <button
                                    wire:click="$set('qualityStatus', 'pending')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $qualityStatus === 'pending' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}"
                                >
                                    <div class="w-8 h-8 rounded-full bg-yellow-400 mb-2"></div>
                                    <span class="text-sm font-medium {{ $qualityStatus === 'pending' ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        Pendiente
                                    </span>
                                </button>

                                {{-- Aprobado --}}
                                <button
                                    wire:click="$set('qualityStatus', 'approved')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $qualityStatus === 'approved' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}"
                                >
                                    <div class="w-8 h-8 rounded-full bg-green-500 mb-2"></div>
                                    <span class="text-sm font-medium {{ $qualityStatus === 'approved' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        Aprobado
                                    </span>
                                </button>

                                {{-- No Aprobado --}}
                                <button
                                    wire:click="$set('qualityStatus', 'rejected')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $qualityStatus === 'rejected' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}"
                                >
                                    <div class="w-8 h-8 rounded-full bg-red-500 mb-2"></div>
                                    <span class="text-sm font-medium {{ $qualityStatus === 'rejected' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        No Aprobado
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- Comentarios --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Comentarios de Calidad
                                @if($qualityStatus === 'rejected')
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <textarea
                                wire:model="qualityComments"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="{{ $qualityStatus === 'rejected' ? 'Describa el motivo del rechazo...' : 'Observaciones adicionales (opcional)...' }}"
                            ></textarea>
                            @if($qualityStatus === 'rejected')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                    * El motivo del rechazo es requerido
                                </p>
                            @endif
                            @error('qualityComments')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button
                            wire:click="closeQualityModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            wire:click="saveQualityStatus"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                        >
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    $wire.on('refresh-display', () => {
        console.log('Display refreshed');
    });

    $wire.on('lotCompleted', () => {
        console.log('New lot completed!');
    });
</script>
@endscript
