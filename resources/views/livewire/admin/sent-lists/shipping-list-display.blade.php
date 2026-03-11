<div class="min-h-screen bg-gray-50 dark:bg-gray-900" wire:poll.30s>
    {{-- Mensajes Flash --}}
    @if (session()->has('message'))
        <div
            class="fixed top-4 right-4 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white px-4 py-3 rounded shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div
            class="fixed top-4 right-4 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white px-4 py-3 rounded shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
                        <svg class="w-4 h-4" wire:loading class="animate-spin" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        <span class="hidden sm:inline">Actualización cada {{ $refreshInterval }}s</span>
                        <span class="sm:hidden">{{ $refreshInterval }}s</span>
                    </div>
                </div>

                {{-- Filtros --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <select wire:model.live="filterDepartment"
                        class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos los Departamentos</option>
                        @foreach (\App\Models\SentList::getDepartments() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filterStatus"
                        class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos los Estados</option>
                        @foreach (\App\Models\SentList::getStatuses() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <button wire:click="toggleCompleted"
                        class="px-4 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        {{ $showCompleted ? 'Ocultar' : 'Mostrar' }} Completados
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido Principal --}}
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 pb-20">
        @foreach ($workOrdersGrouped as $workstationType => $workOrders)
            {{-- Sección por Tipo de Estación --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                {{-- Header de Sección --}}
                <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-indigo-800 dark:bg-gray-900/50">
                    <h2 class="text-white font-semibold text-gray-900 ">{{ $workstationType }}</h2>
                </div>

                {{-- Vista Desktop: Tabla --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    DOC</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    WO #</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Item #</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    # Parte</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Descripción</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Kit</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Insp.</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Prod.</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Cal.</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Emp.</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Cant. WO</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Pz Enviadas</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Cant. Pendiente</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Cant. a Enviar</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                                    Pz Sobrantes</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Fecha Prog. A</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Fecha de Envío</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Fecha de Apertura</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    EG</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($workOrders as $wo)
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

                                    // Piezas sobrantes: si hay registros de empaque usa surplus de empaque, sino pendientes de calidad
                                    $woSobrantes = $allLots->sum(function ($l) {
                                        if ($l->isSurplusReceived()) return 0;
                                        if ($l->hasPackagingRecords()) return $l->getPackagingTotalSurplus();
                                        return $l->getQualityPendingPieces();
                                    });

                                    // Obtener estados de departamentos (simulado por ahora)
                                    $departmentStatuses = [
                                        'materials' => 'pending',
                                        'inspection' => 'pending',
                                        'production' => 'pending',
                                    ];
                                @endphp

                                {{-- Fila Principal de WO --}}
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">WO</td>
                                    <td class="px-4 py-3 text-indigo-600 dark:text-indigo-400 font-medium">
                                        {{ $po->wo }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $part->item_number }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white font-semibold">
                                        {{ $part->number }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs truncate"
                                        title="{{ $part->description }}">{{ $part->description }}</td>
                                    {{-- Celdas vacias para Kit, Insp, Prod, Empaque, Inspeccion en fila WO --}}
                                    <td class="px-4 py-3"></td>
                                    <td class="px-4 py-3"></td>
                                    <td class="px-4 py-3"></td>
                                    <td class="px-4 py-3"></td>
                                    <td class="px-4 py-3"></td>
                                    {{-- Cantidades --}}
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-medium">
                                        {{ number_format($cantWO) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                        {{ number_format($pzEnviadas) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                        {{ number_format($cantAEnviar) }}</td>
                                    <td
                                        class="px-4 py-3 text-right font-semibold bg-yellow-50 dark:bg-yellow-900/20 text-yellow-900 dark:text-yellow-200">
                                        {{ number_format($toSend) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold {{ $woSobrantes > 0 ? 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20' : 'text-gray-400 dark:text-gray-500' }}">
                                        {{ number_format($woSobrantes) }}</td>
                                    {{-- Fechas --}}
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                                        {{ $wo->scheduled_send_date?->format('m/d/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                                        {{ $wo->actual_send_date?->format('m/d/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                                        {{ $wo->created_at->format('m/d/Y') }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                                        {{ $wo->sentList?->id ?? '-' }}</td>
                                </tr>

                                {{-- Filas de Lotes --}}
                                @foreach ($allLots as $lot)
                                    @php
                                        $lotStatusInfo = match ($lot->status) {
                                            \App\Models\Lot::STATUS_PENDING => [
                                                'bg' => 'bg-gray-100 dark:bg-gray-700',
                                                'text' => 'text-gray-700 dark:text-gray-300',
                                                'label' => 'Pendiente',
                                            ],
                                            \App\Models\Lot::STATUS_IN_PROGRESS => [
                                                'bg' => 'bg-indigo-50 dark:bg-indigo-900/30',
                                                'text' => 'text-indigo-700 dark:text-indigo-300',
                                                'label' => 'En Proceso',
                                            ],
                                            \App\Models\Lot::STATUS_COMPLETED => [
                                                'bg' => 'bg-green-50 dark:bg-green-900/30',
                                                'text' => 'text-green-700 dark:text-green-300',
                                                'label' => 'Completado',
                                            ],
                                            default => [
                                                'bg' => 'bg-gray-100 dark:bg-gray-700',
                                                'text' => 'text-gray-700 dark:text-gray-300',
                                                'label' => $lot->status,
                                            ],
                                        };

                                        // Verificar si el lote puede ser inspeccionado
                                        $canInspect = $lot->canBeInspected();
                                        $inspectionStatus = $lot->inspection_status ?? 'pending';

                                        // Color del semaforo de inspeccion
                                        $lotInspectionColor = match ($inspectionStatus) {
                                            'rejected' => 'bg-red-500',
                                            'pending' => 'bg-yellow-400',
                                            'approved' => 'bg-green-500',
                                            default => 'bg-gray-400',
                                        };

                                        // Si no puede ser inspeccionado, mostrar gris
                                        if (!$canInspect) {
                                            $lotInspectionColor = 'bg-gray-300 dark:bg-gray-600';
                                        }

                                        // Obtener razon de bloqueo si existe
                                        $inspectionBlockedReason = $lot->getInspectionBlockedReason();
                                    @endphp
                                    <tr class="bg-gray-50 dark:bg-gray-700/20">
                                        <td class="px-4 py-2 pl-8 text-xs text-gray-600 dark:text-gray-400">Lote</td>
                                        <td class="px-4 py-2 text-xs">
                                            <button wire:click="openLotModal({{ $wo->id }})"
                                                class="text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
                                                {{ $lot->lot_number }}
                                            </button>
                                        </td>
                                        <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300">
                                            {{ $part->item_number }}</td>
                                        <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300 font-medium">
                                            {{ $part->number }}</td>
                                        <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300 max-w-xs truncate"
                                            title="{{ $lot->description ?? $part->description }}">
                                            {{ $lot->description ?? $part->description }}</td>
                                        {{-- Semaforo Kit - Click para abrir modal de Kit (solo CRIMP) --}}
                                        <td class="px-4 py-2 text-center">
                                            @if ($part->is_crimp)
                                                @php
                                                    // Obtener el kit asociado al lote
                                                    $lotKit = $lot->kits->sortByDesc('created_at')->first();
                                                    $lotKitStatus = $lotKit?->status ?? 'none';
                                                    $lotKitColor = match ($lotKitStatus) {
                                                        'in_process' => 'bg-indigo-500',
                                                        'preparing' => 'bg-yellow-400',
                                                        'ready' => 'bg-indigo-500',
                                                        'released' => 'bg-green-500',
                                                        'in_assembly' => 'bg-orange-500',
                                                        default => 'bg-gray-400',
                                                    };
                                                @endphp
                                                <div class="flex items-center justify-center gap-1">
                                                    <button wire:click="openKitModal({{ $lot->id }})"
                                                        class="w-5 h-5 rounded {{ $lotKitColor }} hover:opacity-80 cursor-pointer transition-opacity"
                                                        title="Kit: {{ $lotKit?->kit_number ?? 'Sin kit' }} - {{ $lotKit?->status_label ?? 'N/A' }}"></button>
                                                    <button wire:click="openKitManageModal({{ $lot->id }})"
                                                        class="w-5 h-5 rounded bg-indigo-500 hover:bg-indigo-600 cursor-pointer transition-colors flex items-center justify-center"
                                                        title="Gestionar Kits">
                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @else
                                                {{-- No es CRIMP: lote = kit, semaforo basado en material_status --}}
                                                @php
                                                    $matStatus = $lot->material_status ?? 'pending';
                                                    $matColor = match ($matStatus) {
                                                        'released' => 'bg-green-500',
                                                        'rejected' => 'bg-red-500',
                                                        default => 'bg-gray-400',
                                                    };
                                                    $matLabel = match ($matStatus) {
                                                        'released' => 'Aprobado',
                                                        'rejected' => 'Rechazado',
                                                        default => 'Pendiente',
                                                    };
                                                @endphp
                                                <button wire:click="openMaterialModal({{ $lot->id }})"
                                                    class="w-5 h-5 rounded {{ $matColor }} hover:opacity-80 cursor-pointer transition-opacity"
                                                    title="Material (No CRIMP): {{ $matLabel }}"></button>
                                            @endif
                                        </td>
                                        {{-- Semaforo INSP - Status de Inspeccion por Lote --}}
                                        <td class="px-4 py-2 text-center">
                                            <button wire:click="openInspectionModal({{ $lot->id }})"
                                                class="w-5 h-5 rounded {{ $lotInspectionColor }} {{ $canInspect ? 'hover:opacity-80 cursor-pointer' : 'cursor-not-allowed opacity-60' }} transition-opacity relative inline-flex items-center justify-center"
                                                title="{{ $canInspect ? 'Status de Inspeccion: ' . ucfirst($inspectionStatus) : $inspectionBlockedReason ?? 'Bloqueado' }}">
                                                @if (!$canInspect)
                                                    {{-- Icono de candado para indicar que esta bloqueado --}}
                                                    <svg class="w-3 h-3 text-gray-500 dark:text-gray-400"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </td>
                                        {{-- Semaforo Prod + Botón Pesada --}}
                                        <td class="px-4 py-2 text-center">
                                            @php
                                                $prodTotalWeighed = $lot->weighings->sum('good_pieces') + $lot->weighings->sum('bad_pieces');
                                                $prodReworkPending = $lot->qualityWeighings
                                                    ->where('rework_status', 'pending_rework')
                                                    ->sum('bad_pieces');
                                                $prodTotalToWeigh = $lot->quantity + $prodReworkPending;
                                                $prodSemaphore = 'gray';
                                                if ($prodTotalWeighed > 0 && $prodTotalWeighed >= $prodTotalToWeigh) {
                                                    $prodSemaphore = 'green';
                                                } elseif ($prodTotalWeighed > 0) {
                                                    $prodSemaphore = 'yellow';
                                                }
                                                $prodSemColor = match ($prodSemaphore) {
                                                    'green' => 'bg-green-500',
                                                    'yellow' => 'bg-yellow-400',
                                                    default => 'bg-gray-300 dark:bg-gray-600',
                                                };
                                                $prodRemaining = max(0, $prodTotalToWeigh - $prodTotalWeighed);
                                                $prodTitle = match ($prodSemaphore) {
                                                    'green' => 'Prod: Completado',
                                                    'yellow' => 'Prod: Pendiente (' . number_format($prodRemaining) . ' pz)',
                                                    default => 'Prod: Sin pesadas',
                                                };
                                            @endphp
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="inline-block w-5 h-5 rounded {{ $prodSemColor }}" title="{{ $prodTitle }}"></span>
                                                <button wire:click="openProductionModal({{ $lot->id }})"
                                                    class="w-5 h-5 rounded bg-indigo-500 hover:bg-indigo-600 cursor-pointer transition-colors flex items-center justify-center"
                                                    title="Pesada Lote">
                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v12m6-6H6"/>
                                                    </svg>
                                                </button>
                                                @if ($part->is_crimp)
                                                    <button wire:click="openProdKitModal({{ $lot->id }})"
                                                        class="w-5 h-5 rounded bg-purple-500 hover:bg-purple-600 cursor-pointer transition-colors flex items-center justify-center"
                                                        title="Pesada Kit">
                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        {{-- Semaforo Calidad + Boton Pesada --}}
                                        <td class="px-4 py-2 text-center">
                                            @php
                                                $qualSemaphore = $lot->getQualitySemaphoreStatus();
                                                $qualColor = match ($qualSemaphore) {
                                                    'green' => 'bg-green-500',
                                                    'yellow' => 'bg-yellow-400',
                                                    'gray' => 'bg-gray-300 dark:bg-gray-600',
                                                    default => 'bg-gray-400',
                                                };
                                                $qualHasProduction = $lot->hasProductionWeighings();
                                                $qualPending = $lot->getQualityPendingPieces();
                                                $qualTitle = match ($qualSemaphore) {
                                                    'green' => 'Calidad: Verificado completamente',
                                                    'yellow' => 'Calidad: Pendiente (' . number_format($qualPending) . ' piezas)',
                                                    'gray' => 'Calidad: Sin pesadas de produccion',
                                                    default => 'Calidad',
                                                };
                                            @endphp
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="inline-block w-5 h-5 rounded {{ $qualColor }} {{ !$qualHasProduction ? 'opacity-60' : '' }}"
                                                    title="{{ $qualTitle }}"></span>
                                                @if ($qualHasProduction)
                                                    <button wire:click="openQualityModal({{ $lot->id }})"
                                                        class="w-5 h-5 rounded bg-teal-500 hover:bg-teal-600 cursor-pointer transition-colors flex items-center justify-center"
                                                        title="Calidad Lote">
                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v12m6-6H6"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                                @if ($part->is_crimp)
                                                    <button wire:click="openQualKitModal({{ $lot->id }})"
                                                        class="w-5 h-5 rounded bg-cyan-500 hover:bg-cyan-600 cursor-pointer transition-colors flex items-center justify-center"
                                                        title="Calidad Kit">
                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        {{-- Semaforo Empaque --}}
                                        <td class="px-4 py-2 text-center">
                                            @php
                                                $pkgSem = $lot->getPackagingSemaphoreStatus();
                                                $pkgSemColor = match ($pkgSem) {
                                                    'green' => 'bg-green-500',
                                                    'yellow' => 'bg-yellow-400',
                                                    'blue' => 'bg-blue-500',
                                                    'orange' => 'bg-orange-500',
                                                    default => 'bg-gray-300 dark:bg-gray-600',
                                                };
                                                $pkgSemTitle = match ($pkgSem) {
                                                    'green' => 'Empaque: Completado',
                                                    'yellow' => 'Empaque: Pendiente',
                                                    'blue' => 'Empaque: Viajero recibido, pendiente decisión',
                                                    'orange' => 'Empaque: Cerrado con sobrantes',
                                                    default => 'Empaque: Sin piezas de calidad',
                                                };
                                            @endphp
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="openPackagingModal({{ $lot->id }})"
                                                    class="w-5 h-5 rounded {{ $pkgSemColor }} hover:opacity-80 cursor-pointer transition-opacity"
                                                    title="{{ $pkgSemTitle }}"></button>
                                                @if ($lot->viajero_received)
                                                    <button wire:click="openDecisionModal({{ $lot->id }})"
                                                        class="w-5 h-5 rounded {{ $lot->closure_decision ? 'bg-purple-700' : 'bg-purple-500 hover:bg-purple-600' }} cursor-pointer transition-colors flex items-center justify-center"
                                                        title="Decisión Control de Materiales{{ $lot->closure_decision ? ' (decisión tomada)' : '' }}">
                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                                @if ($lot->viajero_received && $lot->getPackagingTotalSurplus() > 0 && !$lot->surplus_delivered)
                                                    {{-- Paso 1: Empaque entrega sobrante --}}
                                                    <button wire:click="openDeliverMaterialModal({{ $lot->id }})"
                                                        class="w-5 h-5 rounded bg-amber-500 hover:bg-amber-600 cursor-pointer transition-colors flex items-center justify-center"
                                                        title="Entregar Material Sobrante ({{ number_format($lot->getPackagingTotalSurplus()) }} pz)">
                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                        </svg>
                                                    </button>
                                                @elseif ($lot->viajero_received && $lot->getPackagingTotalSurplus() > 0 && $lot->surplus_delivered && !$lot->surplus_received)
                                                    {{-- Paso 2: Entregado, pendiente recepción por Control de Materiales --}}
                                                    <span class="w-5 h-5 rounded bg-amber-600 flex items-center justify-center"
                                                        title="Material entregado, pendiente recepción ({{ number_format($lot->getPackagingTotalSurplus()) }} pz)">
                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </span>
                                                @elseif ($lot->viajero_received && $lot->getPackagingTotalSurplus() > 0 && $lot->surplus_received)
                                                    {{-- Paso 3: Material recibido por Control de Materiales --}}
                                                    <span class="w-5 h-5 rounded bg-green-600 flex items-center justify-center"
                                                        title="Material sobrante recibido ({{ number_format($lot->getPackagingTotalSurplus()) }} pz)">
                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        {{-- Cantidades --}}
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td
                                            class="px-4 py-2 text-right text-xs font-medium text-gray-900 dark:text-white">
                                            {{ number_format($lot->quantity) }}
                                        </td>
                                        @php
                                            $lotSobrantes = $lot->isSurplusReceived() ? 0 : ($lot->hasPackagingRecords() ? $lot->getPackagingTotalSurplus() : $lot->getQualityPendingPieces());
                                        @endphp
                                        <td class="px-4 py-2 text-right text-xs font-medium {{ $lotSobrantes > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400 dark:text-gray-500' }}">
                                            {{ number_format($lotSobrantes) }}
                                        </td>
                                        {{-- Fechas --}}
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400">
                                            {{ $wo->scheduled_send_date?->format('m/d/Y') ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400">
                                            {{ $wo->actual_send_date?->format('m/d/Y') ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400">
                                            {{ $wo->created_at->format('m/d/Y') }}</td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400">-
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Alerta si suma de lotes sobrepasa Cant. WO --}}
                                @php
                                    $totalLotQuantity = $allLots->sum('quantity');
                                    $exceedsWO = $totalLotQuantity > $cantWO;
                                @endphp
                                @if($exceedsWO)
                                    <tr class="bg-red-50 dark:bg-red-900/30">
                                        <td colspan="19" class="px-4 py-2">
                                            <div class="flex items-center text-red-600 dark:text-red-400 text-xs font-semibold">
                                                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                ALERTA: La suma de lotes ({{ number_format($totalLotQuantity) }}) sobrepasa la Cant. WO ({{ number_format($cantWO) }}) por {{ number_format($totalLotQuantity - $cantWO) }} piezas.
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                                {{-- Fila de Total --}}
                                @if ($allLots->count() > 1)
                                    <tr class="bg-gray-100 dark:bg-gray-700/40 font-semibold">
                                        <td colspan="13" class="px-4 py-2 text-right text-gray-900 dark:text-white">
                                            Total:</td>
                                        <td class="px-4 py-2 text-right {{ $exceedsWO ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                            {{ number_format($totalLotQuantity) }}</td>
                                        <td colspan="7"></td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Vista Móvil/Tablet: Cards --}}
                <div class="lg:hidden divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($workOrders as $wo)
                        @php
                            $po = $wo->purchaseOrder;
                            $part = $po->part;
                            $allLots = $wo->lots;
                            $completedLots = $wo->lots->where('status', \App\Models\Lot::STATUS_COMPLETED);
                            $cantWO = $wo->original_quantity; // Cantidad total del WO
                            $pzEnviadas = $wo->sent_pieces; // Piezas enviadas
                            $cantAEnviar = $cantWO - $pzEnviadas; // Cant. a Enviar = Cant. WO - Pz Enviadas
                            $toSend = $cantAEnviar;

                            $woSobrantesMobile = $allLots->sum(function ($l) {
                                if ($l->isSurplusReceived()) return 0;
                                if ($l->hasPackagingRecords()) return $l->getPackagingTotalSurplus();
                                return $l->getQualityPendingPieces();
                            });

                            $departmentStatuses = [
                                'materials' => 'pending',
                                'inspection' => 'pending',
                                'production' => 'pending',
                            ];
                        @endphp

                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span
                                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">WO</span>
                                        <span
                                            class="text-base font-semibold text-indigo-600 dark:text-indigo-400">{{ $po->wo }}</span>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                                        {{ $part->item_number }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $part->description }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cant. WO</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($cantWO) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pz Enviadas</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($pzEnviadas) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cant. Pendiente</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($cantAEnviar) }}</div>
                                </div>
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-2">
                                    <div class="text-xs text-yellow-700 dark:text-yellow-300 mb-1 font-medium">Cant. a
                                        Enviar</div>
                                    <div class="text-sm font-semibold text-yellow-900 dark:text-yellow-200">
                                        {{ number_format($toSend) }}</div>
                                </div>
                                <div class="{{ $woSobrantesMobile > 0 ? 'bg-orange-50 dark:bg-orange-900/20' : '' }} p-2">
                                    <div class="text-xs {{ $woSobrantesMobile > 0 ? 'text-orange-700 dark:text-orange-300 font-medium' : 'text-gray-500 dark:text-gray-400' }} mb-1">Pz Sobrantes</div>
                                    <div class="text-sm font-semibold {{ $woSobrantesMobile > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400 dark:text-gray-500' }}">
                                        {{ number_format($woSobrantesMobile) }}</div>
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-3 gap-2 text-xs pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Prog. A</div>
                                    <div class="text-gray-900 dark:text-white">
                                        {{ $wo->scheduled_send_date?->format('m/d/Y') ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Envío</div>
                                    <div class="text-gray-900 dark:text-white">
                                        {{ $wo->actual_send_date?->format('m/d/Y') ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Apertura</div>
                                    <div class="text-gray-900 dark:text-white">{{ $wo->created_at->format('m/d/Y') }}
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex items-center gap-4 text-xs pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">EG:</span>
                                    <span
                                        class="text-gray-900 dark:text-white font-medium ml-1">{{ $wo->sentList?->id ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">PR:</span>
                                    <span
                                        class="text-gray-900 dark:text-white font-medium ml-1">{{ $wo->priority ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Estado por Departamento
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex flex-col items-center gap-1">
                                        @php
                                            $materialsColor = match ($departmentStatuses['materials']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-indigo-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <button
                                            wire:click="openDepartmentStatusModal({{ $wo->id }}, 'materials')"
                                            class="w-8 h-8 rounded {{ $materialsColor }} hover:opacity-80 transition-opacity"
                                            title="Materiales"></button>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Mat.</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        @php
                                            $inspectionColor = match ($departmentStatuses['inspection']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-indigo-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <button wire:click="openDepartmentStatusModal({{ $wo->id }}, 'inspection')"
                                            class="w-8 h-8 rounded {{ $inspectionColor }} hover:opacity-80 transition-opacity"
                                            title="Inspeccion"></button>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Insp.</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        @php
                                            $productionColor = match ($departmentStatuses['production']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-indigo-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <button
                                            wire:click="openDepartmentStatusModal({{ $wo->id }}, 'production')"
                                            class="w-8 h-8 rounded {{ $productionColor }} hover:opacity-80 transition-opacity"
                                            title="Producción"></button>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Prod.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach ($allLots as $lot)
                            @php
                                $statusInfo = match ($lot->status) {
                                    \App\Models\Lot::STATUS_PENDING => [
                                        'bg' => 'bg-gray-100 dark:bg-gray-700',
                                        'text' => 'text-gray-700 dark:text-gray-300',
                                        'label' => 'Pendiente',
                                    ],
                                    \App\Models\Lot::STATUS_IN_PROGRESS => [
                                        'bg' => 'bg-indigo-50 dark:bg-indigo-900/30',
                                        'text' => 'text-indigo-700 dark:text-indigo-300',
                                        'label' => 'En Proceso',
                                    ],
                                    \App\Models\Lot::STATUS_COMPLETED => [
                                        'bg' => 'bg-green-50 dark:bg-green-900/30',
                                        'text' => 'text-green-700 dark:text-green-300',
                                        'label' => 'Completado',
                                    ],
                                    default => [
                                        'bg' => 'bg-gray-100 dark:bg-gray-700',
                                        'text' => 'text-gray-700 dark:text-gray-300',
                                        'label' => $lot->status,
                                    ],
                                };

                                // Verificar si el lote puede ser inspeccionado
                                $canInspectMobile = $lot->canBeInspected();
                                $inspectionStatusMobile = $lot->inspection_status ?? 'pending';

                                // Color del semaforo de inspeccion
                                $lotInspectionColorMobile = match ($inspectionStatusMobile) {
                                    'rejected' => 'bg-red-500',
                                    'pending' => 'bg-yellow-400',
                                    'approved' => 'bg-green-500',
                                    default => 'bg-gray-400',
                                };

                                // Si no puede ser inspeccionado, mostrar gris
                                if (!$canInspectMobile) {
                                    $lotInspectionColorMobile = 'bg-gray-300 dark:bg-gray-600';
                                }

                                // Obtener razon de bloqueo si existe
                                $inspectionBlockedReasonMobile = $lot->getInspectionBlockedReason();
                            @endphp
                            <div
                                class="p-4 pl-8 bg-gray-50 dark:bg-gray-700/20 space-y-2 border-l-2 border-gray-300 dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <button wire:click="openLotModal({{ $wo->id }})"
                                        class="flex items-center gap-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <span
                                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lote</span>
                                        <span>{{ $lot->lot_number }}</span>
                                    </button>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded {{ $statusInfo['bg'] }} {{ $statusInfo['text'] }}">
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                                    {{ $lot->description ?? $part->description }}</div>

                                {{-- Semaforo de Inspeccion para movil --}}
                                <div
                                    class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Inspeccion:</span>
                                        <button wire:click="openInspectionModal({{ $lot->id }})"
                                            class="w-6 h-6 rounded {{ $lotInspectionColorMobile }} {{ $canInspectMobile ? 'hover:opacity-80 cursor-pointer' : 'cursor-not-allowed opacity-60' }} transition-opacity relative inline-flex items-center justify-center"
                                            title="{{ $canInspectMobile ? 'Status de Inspeccion: ' . ucfirst($inspectionStatusMobile) : $inspectionBlockedReasonMobile ?? 'Bloqueado' }}">
                                            @if (!$canInspectMobile)
                                                <svg class="w-3 h-3 text-gray-500 dark:text-gray-400"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </button>
                                        @if ($canInspectMobile)
                                            <span
                                                class="text-xs {{ $inspectionStatusMobile === 'approved' ? 'text-green-600 dark:text-green-400' : ($inspectionStatusMobile === 'rejected' ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400') }}">
                                                {{ ucfirst($inspectionStatusMobile === 'pending' ? 'Pendiente' : ($inspectionStatusMobile === 'approved' ? 'Aprobado' : 'No Aprobado')) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Bloqueado</span>
                                        @endif
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Cantidad:</span>
                                    <span
                                        class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($lot->quantity) }}</span>
                                </div>
                            </div>
                        @endforeach

                        @if ($allLots->count() > 1)
                            <div class="p-4 bg-gray-100 dark:bg-gray-700/40">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Total:</span>
                                    <span
                                        class="text-base font-semibold text-gray-900 dark:text-white">{{ number_format($allLots->sum('quantity')) }}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        @if ($workOrdersGrouped->isEmpty())
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">No hay lotes</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Los lotes aparecerán aquí automáticamente.</p>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div
        class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Última actualización: <span
                            class="font-medium text-gray-900 dark:text-white">{{ now()->format('d/m/Y H:i:s') }}</span></span>
                </div>
                <div class="flex items-center gap-4 sm:gap-6 text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-1.5">
                        <span
                            class="font-medium text-gray-900 dark:text-white">{{ $workOrdersGrouped->flatten()->count() }}</span>
                        <span>WOs</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span
                            class="font-medium text-gray-900 dark:text-white">{{ $workOrdersGrouped->flatten()->sum(fn($wo) => $wo->lots->count()) }}</span>
                        <span>Lotes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Gestión de Lotes --}}
    @if ($showLotModal && $selectedWorkOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeLotModal"></div>

                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Gestión de Lotes</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    WO: {{ $selectedWorkOrder->purchaseOrder->wo }} | Parte:
                                    {{ $selectedWorkOrder->purchaseOrder->part->number }}
                                </p>
                            </div>
                            <button wire:click="closeLotModal"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                        @if (count($lots) > 0)
                            <div class="space-y-3">
                                @foreach ($lots as $index => $lot)
                                    <div
                                        class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-700">
                                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    No. Lote/Viajero
                                                </label>
                                                <input type="text" wire:model="lots.{{ $index }}.number"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                                    placeholder="Ej: 001">
                                                @error("lots.{$index}.number")
                                                    <span
                                                        class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Cantidad
                                                </label>
                                                <input type="number" wire:model="lots.{{ $index }}.quantity"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                                    placeholder="Ej: 100" min="1">
                                                @error("lots.{$index}.quantity")
                                                    <span
                                                        class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <button wire:click="removeLot({{ $index }})"
                                            class="flex-shrink-0 p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                            title="Eliminar lote">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                                <p class="text-sm">No hay lotes. Agrega uno nuevo.</p>
                            </div>
                        @endif

                        <div class="mt-4">
                            <button wire:click="addLot"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Agregar Lote
                            </button>
                        </div>
                    </div>

                    <div
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeLotModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="saveLots"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors">
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Estado de Departamentos --}}
    @if ($showDepartmentStatusModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeDepartmentStatusModal">
                </div>

                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Estado de Departamentos
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Actualizar estado por
                                    departamento</p>
                            </div>
                            <button wire:click="closeDepartmentStatusModal"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        @foreach (['materials' => 'Materiales', 'inspection' => 'Inspeccion', 'production' => 'Produccion'] as $deptKey => $deptLabel)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ $deptLabel }}
                                </label>
                                <div class="grid grid-cols-4 gap-2">
                                    <button wire:click="updateDepartmentStatus('{{ $deptKey }}', 'rejected')"
                                        class="px-3 py-2 text-xs font-medium border transition-colors {{ $departmentStatuses[$deptKey] === 'rejected' ? 'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400' }}">
                                        Rechazado
                                    </button>
                                    <button wire:click="updateDepartmentStatus('{{ $deptKey }}', 'pending')"
                                        class="px-3 py-2 text-xs font-medium border transition-colors {{ $departmentStatuses[$deptKey] === 'pending' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400' }}">
                                        Pendiente
                                    </button>
                                    <button wire:click="updateDepartmentStatus('{{ $deptKey }}', 'in_progress')"
                                        class="px-3 py-2 text-xs font-medium border transition-colors {{ $departmentStatuses[$deptKey] === 'in_progress' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400' }}">
                                        En Progreso
                                    </button>
                                    <button wire:click="updateDepartmentStatus('{{ $deptKey }}', 'approved')"
                                        class="px-3 py-2 text-xs font-medium border transition-colors {{ $departmentStatuses[$deptKey] === 'approved' ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400' }}">
                                        Aprobado
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeDepartmentStatusModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="saveDepartmentStatuses"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors">
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Status de Inspeccion por Lote --}}
    @if ($showInspectionModal && $selectedLot)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="inspection-modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeInspectionModal"></div>

                {{-- Modal Container --}}
                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="inspection-modal-title"
                                    class="text-lg font-semibold text-gray-900 dark:text-white">Status de Inspeccion -
                                    Lote</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    WO: {{ $selectedLot->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLot->lot_number }}
                                </p>
                            </div>
                            <button wire:click="closeInspectionModal"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-6">
                        {{-- Informacion del Lote --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Informacion del Lote
                            </h4>
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
                                @if ($selectedLot->workOrder->purchaseOrder->part->is_crimp ?? false)
                                    @if ($selectedLot->kits->count() > 0)
                                        <div class="col-span-2">
                                            <span class="text-gray-500 dark:text-gray-400">Kits:</span>
                                            <div class="mt-1 space-y-1">
                                                @foreach ($selectedLot->kits->sortBy('created_at') as $inspKit)
                                                    <div class="flex items-center gap-2">
                                                        @php
                                                            $inspKitColor = match($inspKit->status) {
                                                                'released' => 'bg-green-500',
                                                                'in_process' => 'bg-indigo-500',
                                                                default => 'bg-yellow-500',
                                                            };
                                                            $inspKitLabel = match($inspKit->status) {
                                                                'released' => 'Liberado',
                                                                'in_process' => 'En Proceso',
                                                                default => 'Preparando',
                                                            };
                                                        @endphp
                                                        <span class="w-2 h-2 rounded-full {{ $inspKitColor }}"></span>
                                                        <span class="text-green-600 dark:text-green-400 font-medium text-sm">
                                                            {{ $inspKit->kit_number }}
                                                        </span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">({{ $inspKitLabel }})</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Status de Materiales (Solo lectura) --}}
                        <div
                            class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg border border-green-200 dark:border-green-800">
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full bg-green-500 mr-3"></div>
                                <span class="text-sm font-medium text-green-800 dark:text-green-200">
                                    MAT. Liberado - Habilitado para inspeccion
                                </span>
                            </div>
                        </div>

                        {{-- Status de Inspeccion (Alpine.js para feedback visual inmediato) --}}
                        <div x-data="{ status: $wire.entangle('inspectionStatus') }">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Status de Inspeccion
                            </label>
                            <div class="grid grid-cols-3 gap-3">
                                {{-- Pendiente --}}
                                <button type="button"
                                    x-on:click="status = 'pending'"
                                    :class="status === 'pending'
                                        ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/30 ring-2 ring-yellow-300 dark:ring-yellow-700 shadow-sm'
                                        : 'border-gray-200 dark:border-gray-600 hover:border-yellow-300 dark:hover:border-yellow-600 hover:bg-yellow-50/50 dark:hover:bg-yellow-900/10'"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200 cursor-pointer">
                                    <div
                                        :class="status === 'pending' ? 'ring-2 ring-yellow-300 ring-offset-2 dark:ring-offset-gray-800' : ''"
                                        class="w-8 h-8 rounded-full bg-yellow-400 mb-2"></div>
                                    <span
                                        :class="status === 'pending' ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300'"
                                        class="text-sm font-medium">
                                        Pendiente
                                    </span>
                                </button>

                                {{-- Aprobado --}}
                                <button type="button"
                                    x-on:click="status = 'approved'"
                                    :class="status === 'approved'
                                        ? 'border-green-500 bg-green-50 dark:bg-green-900/30 ring-2 ring-green-300 dark:ring-green-700 shadow-sm'
                                        : 'border-gray-200 dark:border-gray-600 hover:border-green-300 dark:hover:border-green-600 hover:bg-green-50/50 dark:hover:bg-green-900/10'"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200 cursor-pointer">
                                    <div
                                        :class="status === 'approved' ? 'ring-2 ring-green-300 ring-offset-2 dark:ring-offset-gray-800' : ''"
                                        class="w-8 h-8 rounded-full bg-green-500 mb-2"></div>
                                    <span
                                        :class="status === 'approved' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'"
                                        class="text-sm font-medium">
                                        Aprobado
                                    </span>
                                </button>

                                {{-- No Aprobado --}}
                                <button type="button"
                                    x-on:click="status = 'rejected'"
                                    :class="status === 'rejected'
                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/30 ring-2 ring-red-300 dark:ring-red-700 shadow-sm'
                                        : 'border-gray-200 dark:border-gray-600 hover:border-red-300 dark:hover:border-red-600 hover:bg-red-50/50 dark:hover:bg-red-900/10'"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200 cursor-pointer">
                                    <div
                                        :class="status === 'rejected' ? 'ring-2 ring-red-300 ring-offset-2 dark:ring-offset-gray-800' : ''"
                                        class="w-8 h-8 rounded-full bg-red-500 mb-2"></div>
                                    <span
                                        :class="status === 'rejected' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300'"
                                        class="text-sm font-medium">
                                        No Aprobado
                                    </span>
                                </button>
                            </div>

                            {{-- Texto descriptivo del status seleccionado --}}
                            <div class="mt-3 text-sm text-center py-2 px-3 rounded-md transition-all duration-200"
                                :class="{
                                    'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300': status === 'pending',
                                    'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300': status === 'approved',
                                    'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300': status === 'rejected'
                                }">
                                <span x-show="status === 'pending'">Lote pendiente de inspeccion</span>
                                <span x-show="status === 'approved'">Lote aprobado - Habilitado para empaque</span>
                                <span x-show="status === 'rejected'">Lote rechazado - Requiere accion correctiva</span>
                            </div>

                            {{-- Comentarios --}}
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Comentarios de Inspeccion
                                    <span x-show="status === 'rejected'" class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="inspectionComments" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    :placeholder="status === 'rejected' ? 'Describa el motivo del rechazo...' : 'Observaciones adicionales (opcional)...'"
                                ></textarea>
                                <p x-show="status === 'rejected'" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                    * El motivo del rechazo es requerido
                                </p>
                                @error('inspectionComments')
                                    <span
                                        class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeInspectionModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button wire:click="saveInspectionStatus"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors cursor-pointer">
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Status de Kit por Lote (semaphore click — original) --}}
    @if ($showKitModal && $selectedLotForKit)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="kit-modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeKitModal"></div>

                {{-- Modal Container --}}
                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-indigo-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="kit-modal-title" class="text-lg font-semibold text-white">Status de Kit - Lote
                                </h3>
                                <p class="text-sm text-indigo-100 mt-1">
                                    WO: {{ $selectedLotForKit->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLotForKit->lot_number }}
                                </p>
                            </div>
                            <button wire:click="closeKitModal" class="text-white hover:text-indigo-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-6">
                        {{-- Informacion del Lote --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Información del Lote
                            </h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Parte:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                        {{ $selectedLotForKit->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Cantidad:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                        {{ number_format($selectedLotForKit->quantity) }} piezas
                                    </span>
                                </div>
                                @if ($selectedLotForKit->kits->count() > 0)
                                    <div class="col-span-2">
                                        <span class="text-gray-500 dark:text-gray-400">Kits:</span>
                                        <div class="mt-1 space-y-1">
                                            @foreach ($selectedLotForKit->kits->sortBy('created_at') as $kit)
                                                <div class="flex items-center gap-2">
                                                    @php
                                                        $kitStatusColor = match($kit->status) {
                                                            'released' => 'bg-green-500',
                                                            'in_process' => 'bg-indigo-500',
                                                            default => 'bg-yellow-500',
                                                        };
                                                        $kitStatusLabel = match($kit->status) {
                                                            'released' => 'Liberado',
                                                            'in_process' => 'En Proceso',
                                                            default => 'Preparando',
                                                        };
                                                    @endphp
                                                    <span class="w-2 h-2 rounded-full {{ $kitStatusColor }}"></span>
                                                    <span class="text-indigo-600 dark:text-indigo-400 font-medium text-sm">
                                                        {{ $kit->kit_number }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ $kitStatusLabel }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="col-span-2">
                                        <span class="text-yellow-600 dark:text-yellow-400 font-medium">
                                            No hay kit asociado a este lote
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Botón para abrir modal de gestión de kits cuando no hay kit --}}
                        @if (!$selectedKit)
                            <div class="text-center">
                                <button wire:click="switchToKitManageModal({{ $selectedLotForKit->id }})"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Agregar Kits
                                </button>
                            </div>
                        @endif

                        @if ($selectedKit)
                            {{-- Status de Kit (Alpine.js para feedback visual inmediato) --}}
                            <div x-data="{ kitSt: $wire.entangle('kitStatus') }">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Status del Kit
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    {{-- Aprobado --}}
                                    <button type="button"
                                        x-on:click="kitSt = 'released'"
                                        :class="kitSt === 'released'
                                            ? 'border-green-500 bg-green-50 dark:bg-green-900/30 ring-2 ring-green-300 dark:ring-green-700 shadow-sm'
                                            : 'border-gray-200 dark:border-gray-600 hover:border-green-300 dark:hover:border-green-600 hover:bg-green-50/50 dark:hover:bg-green-900/10'"
                                        class="flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200 cursor-pointer">
                                        <div
                                            :class="kitSt === 'released' ? 'ring-2 ring-green-300 ring-offset-2 dark:ring-offset-gray-800' : ''"
                                            class="w-8 h-8 rounded-full bg-green-500 mb-2 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span
                                            :class="kitSt === 'released' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'"
                                            class="text-sm font-medium">
                                            Aprobado
                                        </span>
                                    </button>

                                    {{-- En Proceso --}}
                                    <button type="button"
                                        x-on:click="kitSt = 'in_process'"
                                        :class="kitSt === 'in_process'
                                            ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 ring-2 ring-indigo-300 dark:ring-indigo-700 shadow-sm'
                                            : 'border-gray-200 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-600 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10'"
                                        class="flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200 cursor-pointer">
                                        <div
                                            :class="kitSt === 'in_process' ? 'ring-2 ring-indigo-300 ring-offset-2 dark:ring-offset-gray-800' : ''"
                                            class="w-8 h-8 rounded-full bg-indigo-500 mb-2 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <span
                                            :class="kitSt === 'in_process' ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300'"
                                            class="text-sm font-medium">
                                            En Proceso
                                        </span>
                                    </button>
                                </div>

                                {{-- Texto descriptivo del status seleccionado --}}
                                <div class="mt-3 text-sm text-center py-2 px-3 rounded-md transition-all duration-200"
                                    :class="{
                                        'bg-gray-50 dark:bg-gray-700/20 text-gray-500 dark:text-gray-400': kitSt === 'preparing',
                                        'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300': kitSt === 'released',
                                        'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': kitSt === 'in_process'
                                    }">
                                    <span x-show="kitSt === 'preparing'">Kit pendiente de revision</span>
                                    <span x-show="kitSt === 'released'">Kit aprobado - Listo para produccion</span>
                                    <span x-show="kitSt === 'in_process'">Kit en proceso - En preparacion</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeKitModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        @if ($selectedKit)
                            <button wire:click="saveKitStatus"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                Guardar Cambios
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Gestión de Kits (botón aparte — multi-kit) --}}
    @if ($showKitManageModal && $selectedLotForKitManage)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="kit-manage-modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeKitManageModal"></div>

                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-indigo-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="kit-manage-modal-title" class="text-lg font-semibold text-white">Gestión de Kits</h3>
                                <p class="text-sm text-indigo-100 mt-1">
                                    WO: {{ $selectedLotForKitManage->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLotForKitManage->lot_number }} |
                                    Parte: {{ $selectedLotForKitManage->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                </p>
                            </div>
                            <button wire:click="closeKitManageModal" class="text-white hover:text-indigo-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                        {{-- Info de capacidad del lote --}}
                        @php
                            $mkLotQty = $selectedLotForKitManage->quantity;
                            $mkUsedQty = collect($lotKits)->sum('quantity');
                            $mkRemainingQty = max(0, $mkLotQty - $mkUsedQty);
                        @endphp
                        <div class="mb-4 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                            <div class="grid grid-cols-3 gap-3 text-sm text-center">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block">Cant. Lote</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($mkLotQty) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block">Asignado</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($mkUsedQty) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400 block">Disponible</span>
                                    <span class="font-bold {{ $mkRemainingQty > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($mkRemainingQty) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Lista de kits existentes --}}
                        @if (count($lotKits) > 0)
                            <div class="space-y-3">
                                @foreach ($lotKits as $kit)
                                    @php
                                        $mkStatusColor = match ($kit['status'] ?? 'preparing') {
                                            'released' => 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20',
                                            'in_process' => 'border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20',
                                            'ready' => 'border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20',
                                            default => 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30',
                                        };
                                        $mkStatusLabel = match ($kit['status'] ?? 'preparing') {
                                            'released' => 'Aprobado',
                                            'in_process' => 'En Proceso',
                                            'ready' => 'Listo',
                                            'in_assembly' => 'En ensamble',
                                            default => 'En preparación',
                                        };
                                    @endphp
                                    <div class="flex items-center gap-3 p-3 border rounded-lg {{ $mkStatusColor }}">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                    {{ $kit['kit_number'] }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ number_format($kit['quantity'] ?? 0) }} pz
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full
                                                    {{ match ($kit['status'] ?? 'preparing') {
                                                        'released' => 'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-300',
                                                        'in_process' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800/30 dark:text-indigo-300',
                                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                    } }}">
                                                    {{ $mkStatusLabel }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <button wire:click="updateKitStatus({{ $kit['id'] }}, 'released')"
                                                class="w-7 h-7 rounded-full flex items-center justify-center transition-all
                                                    {{ ($kit['status'] ?? '') === 'released' ? 'bg-green-500 ring-2 ring-green-300 dark:ring-green-700' : 'bg-green-400/50 hover:bg-green-500' }}"
                                                title="Aprobar">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                            <button wire:click="updateKitStatus({{ $kit['id'] }}, 'in_process')"
                                                class="w-7 h-7 rounded-full flex items-center justify-center transition-all
                                                    {{ ($kit['status'] ?? '') === 'in_process' ? 'bg-indigo-500 ring-2 ring-indigo-300 dark:ring-indigo-700' : 'bg-indigo-400/50 hover:bg-indigo-500' }}"
                                                title="En Proceso">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                            <button wire:click="removeKit({{ $kit['id'] }})"
                                                wire:confirm="¿Eliminar este kit?"
                                                class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-300/50 hover:bg-red-400 transition-all ml-1"
                                                title="Eliminar kit">
                                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-10 w-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <p class="text-sm">No hay kits. Agrega uno nuevo.</p>
                            </div>
                        @endif

                        {{-- Formulario crear nuevo kit --}}
                        @if ($showCreateKitForm)
                            <div class="mt-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 p-4 rounded-lg space-y-4">
                                <h4 class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">Crear Nuevo Kit</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">No. Kit *</label>
                                        <input wire:model="newKitNumber" type="text"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="KIT-XXXXXXX-001">
                                        @error('newKitNumber')
                                            <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad *</label>
                                        <input wire:model="newKitQuantity" type="number" min="1"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Ej: 100">
                                        @error('newKitQuantity')
                                            <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="flex gap-3 justify-end">
                                    <button wire:click="closeCreateKitForm"
                                        class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        Cancelar
                                    </button>
                                    <button wire:click="saveNewKitManage"
                                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        Crear Kit
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="mt-4">
                                <button wire:click="openCreateKitForm"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Agregar Kit
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
                        <button wire:click="closeKitManageModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Material (No CRIMP: Lote = Kit) --}}
    @if ($showMaterialModal && $selectedLotForMaterial)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="material-modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeMaterialModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal --}}
                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-white" id="material-modal-title">
                                    Material del Lote
                                </h3>
                                <p class="text-sm text-amber-100 mt-1">
                                    WO: {{ $selectedLotForMaterial->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLotForMaterial->lot_number }}
                                </p>
                            </div>
                            <button wire:click="closeMaterialModal" class="text-white hover:text-amber-200 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-6">
                        {{-- Informacion del Lote --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Información del Lote</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Parte:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                        {{ $selectedLotForMaterial->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Cantidad:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                        {{ number_format($selectedLotForMaterial->quantity) }} piezas
                                    </span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-gray-500 dark:text-gray-400">Lote:</span>
                                    <span class="ml-2 text-indigo-600 dark:text-indigo-400 font-medium">
                                        {{ $selectedLotForMaterial->lot_number }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Status selector (Alpine.js) --}}
                        <div x-data="{ matSt: $wire.entangle('materialStatus') }">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Status del Material
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                {{-- Aprobado --}}
                                <button type="button"
                                    x-on:click="matSt = 'released'"
                                    :class="matSt === 'released'
                                        ? 'border-green-500 bg-green-50 dark:bg-green-900/30 ring-2 ring-green-300 dark:ring-green-700 shadow-sm'
                                        : 'border-gray-200 dark:border-gray-600 hover:border-green-300 dark:hover:border-green-600 hover:bg-green-50/50 dark:hover:bg-green-900/10'"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200 cursor-pointer">
                                    <div
                                        :class="matSt === 'released' ? 'ring-2 ring-green-300 ring-offset-2 dark:ring-offset-gray-800' : ''"
                                        class="w-8 h-8 rounded-full bg-green-500 mb-2 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span
                                        :class="matSt === 'released' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'"
                                        class="text-sm font-medium">
                                        Aprobado
                                    </span>
                                </button>

                                {{-- Rechazado --}}
                                <button type="button"
                                    x-on:click="matSt = 'rejected'"
                                    :class="matSt === 'rejected'
                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/30 ring-2 ring-red-300 dark:ring-red-700 shadow-sm'
                                        : 'border-gray-200 dark:border-gray-600 hover:border-red-300 dark:hover:border-red-600 hover:bg-red-50/50 dark:hover:bg-red-900/10'"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all duration-200 cursor-pointer">
                                    <div
                                        :class="matSt === 'rejected' ? 'ring-2 ring-red-300 ring-offset-2 dark:ring-offset-gray-800' : ''"
                                        class="w-8 h-8 rounded-full bg-red-500 mb-2 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <span
                                        :class="matSt === 'rejected' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300'"
                                        class="text-sm font-medium">
                                        Rechazado
                                    </span>
                                </button>
                            </div>

                            {{-- Texto descriptivo --}}
                            <div class="mt-3 text-sm text-center py-2 px-3 rounded-md transition-all duration-200"
                                :class="{
                                    'bg-gray-50 dark:bg-gray-700/20 text-gray-500 dark:text-gray-400': matSt === 'pending',
                                    'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300': matSt === 'released',
                                    'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300': matSt === 'rejected'
                                }">
                                <span x-show="matSt === 'pending'">Material pendiente de revision</span>
                                <span x-show="matSt === 'released'">Material aprobado - Listo para produccion</span>
                                <span x-show="matSt === 'rejected'">Material rechazado - Requiere correccion</span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeMaterialModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button wire:click="saveMaterialStatus"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition-colors cursor-pointer">
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Empaque por Lote — 4 Fases --}}
    @if ($showPackagingModal && $selectedLotForPackaging)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="packaging-modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closePackagingModal"></div>

                {{-- Modal Container --}}
                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-orange-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="packaging-modal-title" class="text-lg font-semibold text-white">Empaque - Lote {{ $selectedLotForPackaging->lot_number }}</h3>
                                <p class="text-sm text-orange-100 mt-1">
                                    WO: {{ $selectedLotForPackaging->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Parte: {{ $selectedLotForPackaging->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                </p>
                            </div>
                            <button wire:click="closePackagingModal" class="text-white hover:text-orange-200 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 max-h-[75vh] overflow-y-auto space-y-6">

                        {{-- Resumen de piezas --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg text-center">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Disponibles (Calidad)</div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($pkgAvailablePieces) }}</div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg text-center">
                                <div class="text-xs text-green-600 dark:text-green-400 mb-1">Ya Empacadas</div>
                                <div class="text-lg font-bold text-green-700 dark:text-green-300">{{ number_format($pkgAlreadyPacked) }}</div>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg text-center">
                                <div class="text-xs text-yellow-600 dark:text-yellow-400 mb-1">Pendientes</div>
                                <div class="text-lg font-bold text-yellow-700 dark:text-yellow-300">{{ number_format($pkgPendingPieces) }}</div>
                            </div>
                            <div class="bg-orange-50 dark:bg-orange-900/20 p-3 rounded-lg text-center">
                                <div class="text-xs text-orange-600 dark:text-orange-400 mb-1">Sobrantes Total</div>
                                <div class="text-lg font-bold text-orange-700 dark:text-orange-300">{{ number_format($pkgTotalSurplus) }}</div>
                            </div>
                        </div>

                        {{-- ================================================ --}}
                        {{-- FASE 1: Registrar Empaque --}}
                        {{-- ================================================ --}}
                        @if ($pkgAvailablePieces > 0)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 rounded-t-lg">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-orange-500 text-white text-xs flex items-center justify-center font-bold">1</span>
                                        Registrar Empaque
                                    </h4>
                                </div>
                                <div class="p-4 space-y-4">
                                    {{-- Tabla de registros previos --}}
                                    @if (count($pkgRecordsList) > 0)
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-xs">
                                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                                    <tr>
                                                        <th class="px-2 py-2 text-left text-gray-600 dark:text-gray-400">Empacadas</th>
                                                        <th class="px-2 py-2 text-left text-gray-600 dark:text-gray-400">Sobrantes</th>
                                                        <th class="px-2 py-2 text-left text-gray-600 dark:text-gray-400">Ajuste</th>
                                                        <th class="px-2 py-2 text-left text-gray-600 dark:text-gray-400">Fecha</th>
                                                        <th class="px-2 py-2 text-left text-gray-600 dark:text-gray-400">Usuario</th>
                                                        <th class="px-2 py-2 text-center text-gray-600 dark:text-gray-400">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                    @foreach ($pkgRecordsList as $pr)
                                                        <tr class="{{ $pkgAdjustRecordId === $pr['id'] ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                                                            <td class="px-2 py-2 font-medium text-gray-900 dark:text-white">{{ number_format($pr['packed_pieces']) }}</td>
                                                            <td class="px-2 py-2 {{ $pr['surplus_pieces'] > 0 ? 'text-orange-600 dark:text-orange-400 font-medium' : 'text-gray-500' }}">{{ number_format($pr['surplus_pieces']) }}</td>
                                                            <td class="px-2 py-2">
                                                                @if ($pr['adjusted_surplus'] !== null)
                                                                    <span class="text-blue-600 dark:text-blue-400 font-medium">{{ number_format($pr['adjusted_surplus']) }}</span>
                                                                    @if ($pr['adjustment_reason'])
                                                                        <span class="text-gray-400 ml-1" title="{{ $pr['adjustment_reason'] }}">ℹ</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-gray-400">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-2 py-2 text-gray-600 dark:text-gray-400">{{ $pr['packed_at'] }}</td>
                                                            <td class="px-2 py-2 text-gray-600 dark:text-gray-400">{{ $pr['packed_by'] }}</td>
                                                            <td class="px-2 py-2 text-center">
                                                                <div class="flex items-center justify-center gap-1">
                                                                    @if (!$pkgViajeroReceived)
                                                                        <button wire:click="editPackagingRecord({{ $pr['id'] }})"
                                                                            class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 cursor-pointer" title="Editar">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                                        </button>
                                                                        <button wire:click="deletePackagingRecord({{ $pr['id'] }})"
                                                                            wire:confirm="¿Eliminar este registro de empaque?"
                                                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 cursor-pointer" title="Eliminar">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                        </button>
                                                                    @endif
                                                                    @if ($pr['surplus_pieces'] > 0 && !$pkgViajeroReceived)
                                                                        <button wire:click="startAdjustSurplus({{ $pr['id'] }})"
                                                                            class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300 cursor-pointer" title="Ajustar sobrantes">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif

                                    {{-- Formulario de ajuste de sobrantes (Fase 2, inline) --}}
                                    @if ($pkgAdjustRecordId)
                                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 space-y-3">
                                            <h5 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 flex items-center gap-2">
                                                <span class="w-5 h-5 rounded-full bg-yellow-500 text-white text-xs flex items-center justify-center font-bold">2</span>
                                                Ajustar Sobrantes
                                            </h5>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Sobrantes Ajustados</label>
                                                    <input type="number" wire:model="pkgAdjustedSurplus" min="0"
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded focus:ring-1 focus:ring-yellow-500">
                                                    @error('pkgAdjustedSurplus')
                                                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo del Ajuste *</label>
                                                    <input type="text" wire:model="pkgAdjustmentReason"
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded focus:ring-1 focus:ring-yellow-500"
                                                        placeholder="Ej: Reconteo manual">
                                                    @error('pkgAdjustmentReason')
                                                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="flex justify-end gap-2">
                                                <button wire:click="cancelAdjustSurplus"
                                                    class="px-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                                                    Cancelar
                                                </button>
                                                <button wire:click="saveAdjustSurplus"
                                                    class="px-3 py-1.5 text-xs bg-yellow-600 hover:bg-yellow-700 text-white rounded cursor-pointer">
                                                    Guardar Ajuste
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Formulario de nuevo empaque --}}
                                    @if ($pkgPendingPieces > 0 && !$pkgViajeroReceived)
                                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                            <h5 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                                {{ $pkgEditingId ? 'Editar Registro' : 'Nuevo Registro de Empaque' }}
                                            </h5>
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Empacadas *</label>
                                                    <input type="number" wire:model.live="pkgPackedPieces" min="0" max="{{ $pkgPendingPieces }}"
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded focus:ring-1 focus:ring-orange-500">
                                                    @error('pkgPackedPieces')
                                                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Sobrantes</label>
                                                    <input type="number" value="{{ $pkgSurplusPieces }}" readonly
                                                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded cursor-not-allowed">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha/Hora *</label>
                                                    <input type="datetime-local" wire:model="pkgPackedAt"
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded focus:ring-1 focus:ring-orange-500">
                                                    @error('pkgPackedAt')
                                                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios</label>
                                                <textarea wire:model="pkgComments" rows="2"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded focus:ring-1 focus:ring-orange-500"
                                                    placeholder="Observaciones (opcional)..."></textarea>
                                            </div>
                                            <div class="mt-3 flex justify-end gap-2">
                                                @if ($pkgEditingId)
                                                    <button wire:click="cancelEditPackaging"
                                                        class="px-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                                                        Cancelar Edición
                                                    </button>
                                                @endif
                                                <button wire:click="savePackaging"
                                                    class="px-4 py-1.5 text-xs bg-orange-600 hover:bg-orange-700 text-white font-medium rounded cursor-pointer">
                                                    {{ $pkgEditingId ? 'Actualizar' : 'Registrar Empaque' }}
                                                </button>
                                            </div>
                                        </div>
                                    @elseif ($pkgAvailablePieces > 0 && $pkgPendingPieces <= 0 && !$pkgViajeroReceived)
                                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3 text-center">
                                            <span class="text-sm text-green-700 dark:text-green-300 font-medium">Todas las piezas disponibles han sido empacadas.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 text-center">
                                <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay piezas disponibles para empacar. Calidad debe verificar piezas primero.</p>
                            </div>
                        @endif

                        {{-- ================================================ --}}
                        {{-- FASE 3: Recibí Viajero --}}
                        {{-- ================================================ --}}
                        @if ($pkgAlreadyPacked > 0 && !$pkgViajeroReceived)
                            <div class="border border-blue-200 dark:border-blue-700 rounded-lg">
                                <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-700 rounded-t-lg">
                                    <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200 flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center font-bold">3</span>
                                        Recibir Viajero
                                    </h4>
                                </div>
                                <div class="p-4">
                                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded p-4 mb-4">
                                            <div class="grid grid-cols-2 gap-3 text-sm">
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Total Empacadas:</span>
                                                    <span class="ml-2 font-bold text-green-600 dark:text-green-400">{{ number_format($pkgAlreadyPacked) }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Sobrantes Finales:</span>
                                                    <span class="ml-2 font-bold text-orange-600 dark:text-orange-400">{{ number_format($pkgTotalSurplus) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button wire:click="receiveViajero"
                                            wire:confirm="¿Confirma que recibió el viajero? Esta acción no se puede deshacer."
                                            class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Recibí Viajero
                                        </button>
                                </div>
                            </div>
                        @elseif ($pkgViajeroReceived)
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Viajero recibido</span>
                                    </div>
                                    <button wire:click="openDecisionFromPackaging"
                                        class="px-3 py-1.5 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors cursor-pointer">
                                        Ir a Decisión
                                    </button>
                                </div>
                                <button wire:click="reopenPackaging"
                                    wire:confirm="¿Reabrir el empaque? El viajero quedará como no recibido y podrás registrar más piezas."
                                    class="w-full px-3 py-2 text-xs font-medium text-yellow-700 dark:text-yellow-300 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-600 hover:bg-yellow-100 dark:hover:bg-yellow-900/40 rounded-lg transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reabrir Empaque
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
                        <button wire:click="closePackagingModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- MODAL: Decisión Control de Materiales --}}
    {{-- ================================================================ --}}
    @if ($showDecisionModal && $selectedLotForDecision)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="decision-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" wire:click="closeDecisionModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-purple-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="decision-modal-title" class="text-lg font-semibold text-white">Decisión - Control de Materiales</h3>
                                <p class="text-sm text-purple-100 mt-1">
                                    Lote {{ $selectedLotForDecision->lot_number }} |
                                    WO: {{ $selectedLotForDecision->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Parte: {{ $selectedLotForDecision->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                    @if ($decIsCrimp) <span class="ml-1 px-1.5 py-0.5 text-xs bg-purple-800 text-purple-100 rounded">CRIMP</span> @endif
                                </p>
                            </div>
                            <button wire:click="closeDecisionModal" class="text-white hover:text-purple-200 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-5">

                        {{-- Resumen --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg text-center">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Lote</div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($decLotTotal) }}</div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg text-center">
                                <div class="text-xs text-green-600 dark:text-green-400 mb-1">Empacadas</div>
                                <div class="text-lg font-bold text-green-700 dark:text-green-300">{{ number_format($decPacked) }}</div>
                            </div>
                            <div class="bg-orange-50 dark:bg-orange-900/20 p-3 rounded-lg text-center">
                                <div class="text-xs text-orange-600 dark:text-orange-400 mb-1">Sobrantes</div>
                                <div class="text-lg font-bold text-orange-700 dark:text-orange-300">{{ number_format($decSurplus) }}</div>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg text-center">
                                <div class="text-xs text-red-600 dark:text-red-400 mb-1">Faltantes</div>
                                <div class="text-lg font-bold text-red-700 dark:text-red-300">{{ number_format($decMissing) }}</div>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            Faltantes = Total Lote - Empacadas - Sobrantes
                        </p>

                        {{-- Decision options (only if no closure decision yet) --}}
                        @if (!$decClosureDecision)
                            @if ($decSurplus > 0 || $decMissing > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    {{-- Opción 1: Completar Lote --}}
                                    @if ($decMissing > 0)
                                        <button wire:click="decisionCompleteLot"
                                            class="p-4 border-2 border-indigo-200 dark:border-indigo-700 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors cursor-pointer text-center">
                                            <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-indigo-100 dark:bg-indigo-800 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </div>
                                            <div class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">Completar Lote</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Nuevo {{ $decIsCrimp ? 'lote + kit' : 'lote' }} de {{ number_format($decMissing) }} pz</div>
                                        </button>
                                    @endif

                                    {{-- Opción 2: Nuevo Lote --}}
                                    <button wire:click="decisionNewLot"
                                        class="p-4 border-2 border-green-200 dark:border-green-700 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors cursor-pointer text-center">
                                        <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                        </div>
                                        <div class="text-sm font-semibold text-green-700 dark:text-green-300">Nuevo Lote</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ number_format($decMissing) }} pz en {{ $decIsCrimp ? 'lote + kit' : 'lote' }} nuevo</div>
                                    </button>

                                    {{-- Opción 3: Cerrar Lote como está --}}
                                    <button wire:click="decisionCloseAsIs"
                                        wire:confirm="¿Cerrar el lote aceptando {{ number_format($decMissing) }} piezas faltantes?"
                                        class="p-4 border-2 border-orange-200 dark:border-orange-700 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors cursor-pointer text-center">
                                        <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-orange-100 dark:bg-orange-800 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="text-sm font-semibold text-orange-700 dark:text-orange-300">Cerrar Lote</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Aceptar {{ number_format($decMissing) }} pz faltantes</div>
                                    </button>
                                </div>
                            @else
                                {{-- Sin faltantes: cerrar directamente --}}
                                <button wire:click="decisionCloseAsIs"
                                    wire:confirm="¿Cerrar el lote? No hay piezas faltantes."
                                    class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Cerrar Lote (Completo)
                                </button>
                            @endif
                        @else
                            {{-- Decisión ya tomada --}}
                            @php
                                $closureLabel = match ($decClosureDecision) {
                                    'complete_lot' => 'Completar Lote',
                                    'new_lot' => 'Nuevo Lote Creado',
                                    'close_as_is' => 'Lote Cerrado (faltantes aceptados)',
                                    default => $decClosureDecision,
                                };
                                $closureColor = match ($decClosureDecision) {
                                    'complete_lot' => 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-700 text-indigo-800 dark:text-indigo-200',
                                    'new_lot' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700 text-green-800 dark:text-green-200',
                                    'close_as_is' => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-700 text-orange-800 dark:text-orange-200',
                                    default => 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200',
                                };
                            @endphp
                            <div class="border rounded-lg p-3 {{ $closureColor }}">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm font-medium">Decisión: {{ $closureLabel }}</span>
                                </div>
                            </div>

                            {{-- Estado de entrega de sobrantes --}}
                            @if ($decSurplus > 0)
                                @if (!$decSurplusDelivered)
                                    {{-- Paso 1: Empaque aún no ha entregado el sobrante --}}
                                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-amber-800 dark:text-amber-200">Pendiente: Empaque debe entregar {{ number_format($decSurplus) }} pz sobrantes</span>
                                        </div>
                                    </div>
                                @elseif (!$decSurplusReceived)
                                    {{-- Paso 2: Entregado, pendiente recepción --}}
                                    <div class="border border-red-200 dark:border-red-700 rounded-lg p-4">
                                        <h5 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-3">Pendiente: Recepción de Material Sobrante</h5>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                            Empaque entregó <strong class="text-orange-600">{{ number_format($decSurplus) }}</strong> piezas sobrantes. Confirmar recepción.
                                        </p>
                                        <button wire:click="confirmSurplusReceived"
                                            wire:confirm="¿Confirma que se recibieron {{ number_format($decSurplus) }} piezas sobrantes?"
                                            class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                            Material Recibido
                                        </button>
                                    </div>
                                @else
                                    {{-- Paso 3: Todo completado --}}
                                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm font-medium text-green-800 dark:text-green-200">Material sobrante recibido. Lote completado.</span>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            {{-- Reabrir Lote --}}
                            <button wire:click="reopenLot"
                                wire:confirm="¿Desea reabrir este lote y anular la decisión tomada?"
                                class="w-full px-4 py-3 border-2 border-yellow-400 dark:border-yellow-600 text-yellow-700 dark:text-yellow-300 font-semibold rounded-lg hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reabrir Lote
                            </button>

                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
                        <button wire:click="closeDecisionModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- MODAL: Crear Lote (from Decision) --}}
    {{-- ================================================================ --}}
    @if ($showCreateLotFormModal && $selectedLotForDecision)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="create-lot-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" wire:click="closeCreateLotFormModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-indigo-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="create-lot-modal-title" class="text-lg font-semibold text-white">
                                    Crear {{ $decIsCrimp ? 'Lote + Kit' : 'Nuevo Lote' }}
                                </h3>
                                <p class="text-sm text-indigo-100 mt-1">
                                    {{ $createLotType === 'complete' ? 'Completar lote con piezas faltantes' : 'Cerrar lote actual y crear nuevo' }}
                                </p>
                            </div>
                            <button wire:click="closeCreateLotFormModal" class="text-white hover:text-indigo-200 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-5 space-y-4">
                        {{-- Info banner --}}
                        @if ($decIsCrimp)
                            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-3">
                                <p class="text-xs text-purple-700 dark:text-purple-300">
                                    <strong>Parte con CRIMP:</strong> Se creará un lote y un kit automáticamente. El kit pasará por el flujo Kit → Producción → Calidad.
                                </p>
                            </div>
                        @endif

                        {{-- Lot Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre / Número de Lote</label>
                            <input type="text" wire:model="createLotName"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('createLotName')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Quantity --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad (piezas)</label>
                            <input type="number" wire:model="createLotQuantity" min="1"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('createLotQuantity')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Piezas faltantes del Lote: {{ number_format($decMissing) }}
                            </p>
                        </div>

                        {{-- Summary --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-sm">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Se creará:</span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    Lote "{{ $createLotName }}" — {{ number_format($createLotQuantity) }} pz
                                </span>
                            </div>
                            @if ($decIsCrimp)
                                <div class="flex justify-between text-gray-600 dark:text-gray-400 mt-1">
                                    <span>Kit automático:</span>
                                    <span class="font-medium text-purple-700 dark:text-purple-300">Sí (CRIMP)</span>
                                </div>
                            @endif
                            @if ($createLotType === 'new_lot')
                                <div class="flex justify-between text-gray-600 dark:text-gray-400 mt-1">
                                    <span>Lote actual:</span>
                                    <span class="font-medium text-orange-600">Se cerrará</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3">
                        <button wire:click="closeCreateLotFormModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button wire:click="confirmCreateLot"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors cursor-pointer">
                            Crear {{ $decIsCrimp ? 'Lote + Kit' : 'Lote' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- MODAL: Entregar Material Sobrante --}}
    {{-- ================================================================ --}}
    @if ($showDeliverMaterialModal && $selectedLotForDelivery)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeDeliverMaterialModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-200 dark:border-gray-700">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-amber-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-white">Entregar Material</h3>
                                <p class="text-sm text-amber-100 mt-1">
                                    Lote {{ $selectedLotForDelivery->lot_number }} |
                                    WO: {{ $selectedLotForDelivery->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Parte: {{ $selectedLotForDelivery->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                </p>
                            </div>
                            <button wire:click="closeDeliverMaterialModal" class="text-white hover:text-amber-200 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-5 space-y-4">
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-4 text-center">
                            <p class="text-sm text-amber-800 dark:text-amber-200 mb-1">Material sobrante por entregar</p>
                            <p class="text-3xl font-bold text-amber-700 dark:text-amber-300">{{ number_format($deliverSurplusAmount) }} <span class="text-sm font-normal">piezas</span></p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-sm space-y-1">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Lote:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $selectedLotForDelivery->lot_number }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Cantidad del lote:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($selectedLotForDelivery->quantity) }} pz</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Empacadas:</span>
                                <span class="font-medium text-green-600 dark:text-green-400">{{ number_format($selectedLotForDelivery->getPackagingPackedPieces()) }} pz</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Sobrantes:</span>
                                <span class="font-medium text-orange-600 dark:text-orange-400">{{ number_format($deliverSurplusAmount) }} pz</span>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            Al confirmar, se registrará que el material sobrante fue entregado a Control de Materiales.
                        </p>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3">
                        <button wire:click="closeDeliverMaterialModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button wire:click="confirmDeliverMaterial"
                            wire:confirm="¿Confirma la entrega de {{ number_format($deliverSurplusAmount) }} piezas sobrantes del Lote {{ $selectedLotForDelivery->lot_number }}?"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition-colors cursor-pointer flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Confirmar Entrega
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Pesada (Calidad) por Lote --}}
    @if ($showQualityModal && $selectedLotForQuality)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="quality-modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeQualityModal"></div>

                {{-- Modal Container --}}
                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-teal-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="quality-modal-title" class="text-lg font-semibold text-white">Pesada de Calidad - Lote</h3>
                                <p class="text-sm text-teal-100 mt-1">
                                    WO: {{ $selectedLotForQuality->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLotForQuality->lot_number }}
                                </p>
                            </div>
                            <button wire:click="closeQualityModal" class="text-white hover:text-teal-200 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-5 max-h-[70vh] overflow-y-auto">
                        {{-- Info del Lote y Produccion --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Parte:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                        {{ $selectedLotForQuality->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Lote:</span>
                                    <span class="ml-2 text-indigo-600 dark:text-indigo-400 font-medium">
                                        {{ $selectedLotForQuality->lot_number }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Resumen de Produccion --}}
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 p-4 rounded-lg">
                            <h4 class="text-sm font-semibold text-indigo-700 dark:text-indigo-300 mb-3">Resumen de Produccion</h4>
                            <div class="grid grid-cols-3 gap-3 text-sm">
                                <div class="text-center">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pz Pesadas Prod.</div>
                                    <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($qualProductionGoodPieces) }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Ya Verificadas</div>
                                    <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($qualAlreadyWeighed) }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pendientes</div>
                                    <div class="text-lg font-bold {{ $qualRemainingPieces > 0 ? 'text-teal-600 dark:text-teal-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ number_format($qualRemainingPieces) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Historial de pesadas de calidad --}}
                        @if (count($qualWeighingsList) > 0)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Historial de Pesadas de Calidad</h4>
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-gray-600 dark:text-gray-400">Fecha</th>
                                                <th class="px-3 py-2 text-right text-green-600 dark:text-green-400">Aprobadas</th>
                                                <th class="px-3 py-2 text-right text-red-600 dark:text-red-400">Rechazadas</th>
                                                <th class="px-3 py-2 text-left text-gray-600 dark:text-gray-400">Por</th>
                                                <th class="px-3 py-2 text-center text-gray-600 dark:text-gray-400">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($qualWeighingsList as $qw)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $qw['weighed_at'] }}</td>
                                                    <td class="px-3 py-2 text-right font-medium text-green-600 dark:text-green-400">{{ number_format($qw['good_pieces']) }}</td>
                                                    <td class="px-3 py-2 text-right font-medium text-red-600 dark:text-red-400">
                                                        {{ number_format($qw['bad_pieces']) }}
                                                        @if ($qw['bad_pieces'] > 0)
                                                            <span class="ml-1 text-gray-400">(descarte)</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $qw['weighed_by'] }}</td>
                                                    <td class="px-3 py-2 text-center">
                                                        <div class="flex items-center justify-center gap-1">
                                                            <button wire:click="editQualityWeighing({{ $qw['id'] }})"
                                                                class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300" title="Editar">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                            </button>
                                                            <button wire:click="deleteQualityWeighing({{ $qw['id'] }})"
                                                                wire:confirm="¿Eliminar esta pesada de calidad?"
                                                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300" title="Eliminar">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- Formulario de nueva/editar pesada --}}
                        @if ($qualRemainingPieces > 0 || $qualEditingId)
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-5">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $qualEditingId ? 'Editar Pesada de Calidad' : 'Nueva Pesada de Calidad' }}
                                    </h4>
                                    @if ($qualEditingId)
                                        <button wire:click="cancelEditQuality"
                                            class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 underline">
                                            Cancelar edicion
                                        </button>
                                    @endif
                                </div>

                                {{-- Pendiente de verificar --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pendiente de Verificar</label>
                                    <div class="w-full px-3 py-2 border rounded-lg font-bold text-lg text-center
                                        border-teal-300 dark:border-teal-600 bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300">
                                        {{ number_format($qualRemainingPieces) }} piezas
                                    </div>
                                </div>

                                {{-- Piezas aprobadas y rechazadas --}}
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Aprobadas *</label>
                                        <input wire:model="qualGoodPieces" type="number" min="0"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            placeholder="0">
                                        @error('qualGoodPieces')
                                            <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Rechazadas *</label>
                                        <input wire:model="qualBadPieces" type="number" min="0"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            placeholder="0">
                                        @error('qualBadPieces')
                                            <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Info de descarte --}}
                                @if ($qualBadPieces > 0)
                                    <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 rounded-lg">
                                        <div class="flex items-center text-sm text-red-700 dark:text-red-300">
                                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            Las {{ number_format($qualBadPieces) }} piezas rechazadas seran descartadas.
                                        </div>
                                    </div>
                                @endif

                                {{-- Fecha y hora --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y Hora *</label>
                                    <input wire:model="qualWeighedAt" type="datetime-local"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                    @error('qualWeighedAt')
                                        <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Comentarios --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios</label>
                                    <textarea wire:model="qualComments" rows="2"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                        placeholder="Observaciones (opcional)..."></textarea>
                                </div>
                            </div>
                        @else
                            {{-- Lote completamente verificado --}}
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4 rounded-lg text-center">
                                <svg class="w-8 h-8 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm font-medium text-green-700 dark:text-green-300">
                                    Todas las piezas de produccion han sido verificadas por Calidad.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeQualityModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cerrar
                        </button>
                        @if ($qualRemainingPieces > 0 || $qualEditingId)
                            <button wire:click="saveQuality"
                                class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-colors cursor-pointer">
                                {{ $qualEditingId ? 'Actualizar Pesada' : 'Registrar Pesada' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Pesada (Producción) por Lote --}}
    @if ($showProductionModal && $selectedLotForProduction)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="production-modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeProductionModal"></div>

                {{-- Modal Container --}}
                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-indigo-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="production-modal-title" class="text-lg font-semibold text-white">Nueva Pesada - Producción</h3>
                                <p class="text-sm text-indigo-100 mt-1">
                                    WO: {{ $selectedLotForProduction->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLotForProduction->lot_number }}
                                </p>
                            </div>
                            <button wire:click="closeProductionModal" class="text-white hover:text-indigo-200 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-5">
                        {{-- Info del Lote --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Parte:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                        {{ $selectedLotForProduction->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Lote:</span>
                                    <span class="ml-2 text-indigo-600 dark:text-indigo-400 font-medium">
                                        {{ $selectedLotForProduction->lot_number }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Cantidad del lote y pendiente de pesar --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad del Lote</label>
                                <div class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm rounded-lg font-semibold">
                                    {{ number_format($prodQuantity) }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ya Pesadas</label>
                                <div class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm rounded-lg font-semibold">
                                    {{ number_format($prodAlreadyWeighed) }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pendiente de Pesar</label>
                            <div class="w-full px-3 py-2 border rounded-lg font-bold text-lg text-center
                                {{ $prodRemainingPieces > 0 ? 'border-indigo-300 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'border-green-300 dark:border-green-600 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300' }}">
                                {{ number_format($prodRemainingPieces) }} piezas
                                @if ($prodRemainingPieces <= 0)
                                    <span class="text-xs font-normal ml-2">(Lote completamente pesado)</span>
                                @endif
                            </div>
                        </div>

                        {{-- Piezas pesadas --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Pesadas *</label>
                            <input wire:model="prodWeighedPieces" type="number" min="0"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="0">
                            @error('prodWeighedPieces')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Fecha y hora --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y Hora de Pesada *</label>
                            <input wire:model="prodWeighedAt" type="datetime-local"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('prodWeighedAt')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Comentarios --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios</label>
                            <textarea wire:model="prodComments" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Observaciones (opcional)..."></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeProductionModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button wire:click="saveProduction"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors cursor-pointer">
                            Registrar Pesada
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Pesada Producción por Kit (CRIMP only) --}}
    @if ($showProdKitModal && $selectedLotForProdKit)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="prod-kit-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeProdKitModal"></div>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-purple-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="prod-kit-modal-title" class="text-lg font-semibold text-white">Pesada Producción — Kit</h3>
                                <p class="text-sm text-purple-100 mt-1">
                                    WO: {{ $selectedLotForProdKit->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLotForProdKit->lot_number }}
                                </p>
                            </div>
                            <button wire:click="closeProdKitModal" class="text-white hover:text-purple-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-4">
                        {{-- Kit selector --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kit *</label>
                            <select wire:model.live="prodKitSelectedId"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">-- Seleccionar Kit --</option>
                                @foreach ($prodKitKits as $pk)
                                    <option value="{{ $pk['id'] }}">{{ $pk['kit_number'] }} ({{ number_format($pk['quantity']) }} pz — Pend: {{ number_format($pk['remaining']) }})</option>
                                @endforeach
                            </select>
                            @error('prodKitSelectedId')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Info del kit seleccionado --}}
                        @if ($prodKitSelectedId)
                            <div class="grid grid-cols-3 gap-3">
                                <div class="text-center bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">Cant. Kit</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($prodKitQuantity) }}</span>
                                </div>
                                <div class="text-center bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">Pesadas</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($prodKitAlreadyWeighed) }}</span>
                                </div>
                                <div class="text-center bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">Pendiente</span>
                                    <span class="font-bold {{ $prodKitRemainingPieces > 0 ? 'text-purple-600 dark:text-purple-400' : 'text-green-600 dark:text-green-400' }}">{{ number_format($prodKitRemainingPieces) }}</span>
                                </div>
                            </div>
                        @endif

                        {{-- Piezas pesadas --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Pesadas *</label>
                            <input wire:model="prodKitWeighedPieces" type="number" min="0"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="0">
                            @error('prodKitWeighedPieces')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Fecha --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y Hora *</label>
                            <input wire:model="prodKitWeighedAt" type="datetime-local"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @error('prodKitWeighedAt')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Comentarios --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios</label>
                            <textarea wire:model="prodKitComments" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="Observaciones (opcional)..."></textarea>
                        </div>
                    </div>
                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeProdKitModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="saveProdKit"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                            Registrar Pesada Kit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Pesada Calidad por Kit (CRIMP only) --}}
    @if ($showQualKitModal && $selectedLotForQualKit)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="qual-kit-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeQualKitModal"></div>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-cyan-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="qual-kit-modal-title" class="text-lg font-semibold text-white">Pesada Calidad — Kit</h3>
                                <p class="text-sm text-cyan-100 mt-1">
                                    WO: {{ $selectedLotForQualKit->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLotForQualKit->lot_number }}
                                </p>
                            </div>
                            <button wire:click="closeQualKitModal" class="text-white hover:text-cyan-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-4">
                        {{-- Kit selector --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kit *</label>
                            <select wire:model.live="qualKitSelectedId"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                <option value="">-- Seleccionar Kit --</option>
                                @foreach ($qualKitKits as $qk)
                                    <option value="{{ $qk['id'] }}">{{ $qk['kit_number'] }} (Prod: {{ number_format($qk['prod_good']) }} — Pend: {{ number_format($qk['remaining']) }})</option>
                                @endforeach
                            </select>
                            @error('qualKitSelectedId')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Info del kit seleccionado --}}
                        @if ($qualKitSelectedId)
                            <div class="grid grid-cols-3 gap-3">
                                <div class="text-center bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">Prod. Buenas</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($qualKitProdGoodPieces) }}</span>
                                </div>
                                <div class="text-center bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">Verificadas</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($qualKitAlreadyWeighed) }}</span>
                                </div>
                                <div class="text-center bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">Pendiente</span>
                                    <span class="font-bold {{ $qualKitRemainingPieces > 0 ? 'text-cyan-600 dark:text-cyan-400' : 'text-green-600 dark:text-green-400' }}">{{ number_format($qualKitRemainingPieces) }}</span>
                                </div>
                            </div>
                        @endif

                        {{-- Piezas aprobadas --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Aprobadas *</label>
                                <input wire:model="qualKitGoodPieces" type="number" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                    placeholder="0">
                                @error('qualKitGoodPieces')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Rechazadas *</label>
                                <input wire:model="qualKitBadPieces" type="number" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                    placeholder="0">
                                @error('qualKitBadPieces')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Fecha --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y Hora *</label>
                            <input wire:model="qualKitWeighedAt" type="datetime-local"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            @error('qualKitWeighedAt')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Comentarios --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios</label>
                            <textarea wire:model="qualKitComments" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                placeholder="Observaciones (opcional)..."></textarea>
                        </div>
                    </div>
                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeQualKitModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="saveQualKit"
                            class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white font-medium rounded-lg transition-colors">
                            Registrar Pesada Calidad Kit
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
