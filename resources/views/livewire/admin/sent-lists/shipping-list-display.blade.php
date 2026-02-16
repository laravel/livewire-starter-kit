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
                        class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los Departamentos</option>
                        @foreach (\App\Models\SentList::getDepartments() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filterStatus"
                        class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
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
                <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-blue-800 dark:bg-gray-900/50">
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
                                    <td class="px-4 py-3 text-blue-600 dark:text-blue-400 font-medium">
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
                                                'bg' => 'bg-blue-50 dark:bg-blue-900/30',
                                                'text' => 'text-blue-700 dark:text-blue-300',
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
                                                class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
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
                                        {{-- Semaforo Kit - Click para abrir modal de Kit --}}
                                        <td class="px-4 py-2 text-center">
                                            @php
                                                // Obtener el kit asociado al lote
                                                $lotKit = $lot->kits->sortByDesc('created_at')->first();
                                                $lotKitStatus = $lotKit?->status ?? 'none';
                                                $lotKitColor = match ($lotKitStatus) {
                                                    'rejected' => 'bg-red-500',
                                                    'preparing' => 'bg-yellow-400',
                                                    'ready' => 'bg-blue-500',
                                                    'released' => 'bg-green-500',
                                                    'in_assembly' => 'bg-orange-500',
                                                    default => 'bg-gray-400',
                                                };
                                            @endphp
                                            <button wire:click="openKitModal({{ $lot->id }})"
                                                class="w-5 h-5 rounded {{ $lotKitColor }} hover:opacity-80 cursor-pointer transition-opacity"
                                                title="Kit: {{ $lotKit?->kit_number ?? 'Sin kit' }} - {{ $lotKit?->status_label ?? 'N/A' }}"></button>
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
                                                    title="Registrar pesada">
                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v12m6-6H6"/>
                                                    </svg>
                                                </button>
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
                                                        title="Registrar pesada de calidad">
                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v12m6-6H6"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        {{-- Semaforo Empaque --}}
                                        <td class="px-4 py-2 text-center">
                                            @php
                                                $packagingStatus = $lot->packaging_status ?? 'pending';
                                                $packagingColor = match ($packagingStatus) {
                                                    'rejected' => 'bg-red-500',
                                                    'pending' => 'bg-yellow-400',
                                                    'approved' => 'bg-green-500',
                                                    default => 'bg-gray-400',
                                                };
                                            @endphp
                                            <button wire:click="openPackagingModal({{ $lot->id }})"
                                                class="w-5 h-5 rounded {{ $packagingColor }} hover:opacity-80 cursor-pointer transition-opacity"
                                                title="Empaque: {{ ucfirst($packagingStatus) }}"></button>
                                        </td>
                                        {{-- Cantidades --}}
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td
                                            class="px-4 py-2 text-right text-xs font-medium text-gray-900 dark:text-white">
                                            {{ number_format($lot->quantity) }}
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
                                        <td colspan="18" class="px-4 py-2">
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
                                        <td colspan="6"></td>
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
                                            class="text-base font-semibold text-blue-600 dark:text-blue-400">{{ $po->wo }}</span>
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
                                                'in_progress' => 'bg-blue-500',
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
                                                'in_progress' => 'bg-blue-500',
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
                                                'in_progress' => 'bg-blue-500',
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
                                        'bg' => 'bg-blue-50 dark:bg-blue-900/30',
                                        'text' => 'text-blue-700 dark:text-blue-300',
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
                                        class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
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
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
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
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
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
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors">
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
                                        class="px-3 py-2 text-xs font-medium border transition-colors {{ $departmentStatuses[$deptKey] === 'in_progress' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400' }}">
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
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors">
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
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
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
                                @php
                                    $releasedKit = $selectedLot->getReleasedKit();
                                @endphp
                                @if ($releasedKit)
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
                        <div
                            class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg border border-green-200 dark:border-green-800">
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full bg-green-500 mr-3"></div>
                                <span class="text-sm font-medium text-green-800 dark:text-green-200">
                                    MAT. Liberado - Habilitado para inspeccion
                                </span>
                            </div>
                        </div>

                        {{-- Status de Inspeccion --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Status de Inspeccion
                            </label>
                            <div class="grid grid-cols-3 gap-3">
                                {{-- Pendiente --}}
                                <button wire:click="setInspectionStatus('pending')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $inspectionStatus === 'pending' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                    <div class="w-8 h-8 rounded-full bg-yellow-400 mb-2"></div>
                                    <span
                                        class="text-sm font-medium {{ $inspectionStatus === 'pending' ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        Pendiente
                                    </span>
                                </button>

                                {{-- Aprobado --}}
                                <button wire:click="setInspectionStatus('approved')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $inspectionStatus === 'approved' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                    <div class="w-8 h-8 rounded-full bg-green-500 mb-2"></div>
                                    <span
                                        class="text-sm font-medium {{ $inspectionStatus === 'approved' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        Aprobado
                                    </span>
                                </button>

                                {{-- No Aprobado --}}
                                <button wire:click="setInspectionStatus('rejected')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $inspectionStatus === 'rejected' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                    <div class="w-8 h-8 rounded-full bg-red-500 mb-2"></div>
                                    <span
                                        class="text-sm font-medium {{ $inspectionStatus === 'rejected' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        No Aprobado
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- Comentarios --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Comentarios de Inspeccion
                                @if ($inspectionStatus === 'rejected')
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <textarea wire:model="inspectionComments" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="{{ $inspectionStatus === 'rejected' ? 'Describa el motivo del rechazo...' : 'Observaciones adicionales (opcional)...' }}"></textarea>
                            @if ($inspectionStatus === 'rejected')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                    * El motivo del rechazo es requerido
                                </p>
                            @endif
                            @error('inspectionComments')
                                <span
                                    class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeInspectionModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="saveInspectionStatus"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Status de Kit por Lote --}}
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
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-blue-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="kit-modal-title" class="text-lg font-semibold text-white">Status de Kit - Lote
                                </h3>
                                <p class="text-sm text-blue-100 mt-1">
                                    WO: {{ $selectedLotForKit->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLotForKit->lot_number }}
                                </p>
                            </div>
                            <button wire:click="closeKitModal" class="text-white hover:text-blue-200">
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
                                @if ($selectedKit)
                                    <div class="col-span-2">
                                        <span class="text-gray-500 dark:text-gray-400">Kit:</span>
                                        <span class="ml-2 text-blue-600 dark:text-blue-400 font-medium">
                                            {{ $selectedKit->kit_number }}
                                        </span>
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

                        {{-- Formulario crear kit cuando no hay kit --}}
                        @if (!$selectedKit && !$showCreateKitForm)
                            <div class="text-center">
                                <button wire:click="openCreateKitForm"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Agregar Kit
                                </button>
                            </div>
                        @endif

                        @if ($showCreateKitForm && !$selectedKit)
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 rounded-lg space-y-4">
                                <h4 class="text-sm font-semibold text-blue-700 dark:text-blue-300">Crear Nuevo Kit</h4>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número de Kit *</label>
                                    <input wire:model="newKitNumber" type="text"
                                        class="w-full p-5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="KIT-XXXXXXX-001">
                                    @error('newKitNumber')
                                        <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="flex gap-3 justify-end">
                                    <button wire:click="closeCreateKitForm"
                                        class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        Cancelar
                                    </button>
                                    <button wire:click="saveNewKit"
                                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        Crear Kit
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if ($selectedKit)
                            {{-- Status de Kit --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Status del Kit
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    {{-- Aprobado --}}
                                    <button wire:click="setKitStatus('released')"
                                        class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $kitStatus === 'released' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                        <div
                                            class="w-8 h-8 rounded-full bg-green-500 mb-2 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span
                                            class="text-sm font-medium {{ $kitStatus === 'released' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300' }}">
                                            Aprobado
                                        </span>
                                    </button>

                                    {{-- Rechazado --}}
                                    <button wire:click="setKitStatus('rejected')"
                                        class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $kitStatus === 'rejected' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                        <div
                                            class="w-8 h-8 rounded-full bg-red-500 mb-2 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                        <span
                                            class="text-sm font-medium {{ $kitStatus === 'rejected' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }}">
                                            Rechazado
                                        </span>
                                    </button>
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
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                Guardar Cambios
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Status de Empaque por Lote --}}
    @if ($showPackagingModal && $selectedLotForPackaging)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="packaging-modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closePackagingModal"></div>

                {{-- Modal Container --}}
                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-orange-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="packaging-modal-title" class="text-lg font-semibold text-white">Status de Empaque - Lote</h3>
                                <p class="text-sm text-orange-100 mt-1">
                                    WO: {{ $selectedLotForPackaging->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLotForPackaging->lot_number }}
                                </p>
                            </div>
                            <button wire:click="closePackagingModal" class="text-white hover:text-orange-200">
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
                                        {{ $selectedLotForPackaging->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Cantidad:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">
                                        {{ number_format($selectedLotForPackaging->quantity) }} piezas
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Status de Empaque --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Status de Empaque
                            </label>
                            <div class="grid grid-cols-3 gap-3">
                                {{-- Pendiente --}}
                                <button wire:click="setPackagingStatus('pending')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $packagingStatus === 'pending' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                    <div class="w-8 h-8 rounded-full bg-yellow-400 mb-2"></div>
                                    <span class="text-sm font-medium {{ $packagingStatus === 'pending' ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        Pendiente
                                    </span>
                                </button>

                                {{-- Aprobado --}}
                                <button wire:click="setPackagingStatus('approved')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $packagingStatus === 'approved' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                    <div class="w-8 h-8 rounded-full bg-green-500 mb-2"></div>
                                    <span class="text-sm font-medium {{ $packagingStatus === 'approved' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        Aprobado
                                    </span>
                                </button>

                                {{-- Rechazado --}}
                                <button wire:click="setPackagingStatus('rejected')"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $packagingStatus === 'rejected' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                    <div class="w-8 h-8 rounded-full bg-red-500 mb-2"></div>
                                    <span class="text-sm font-medium {{ $packagingStatus === 'rejected' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }}">
                                        Rechazado
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- Comentarios --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Comentarios de Empaque
                                @if ($packagingStatus === 'rejected')
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <textarea wire:model="packagingComments" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="{{ $packagingStatus === 'rejected' ? 'Describa el motivo del rechazo...' : 'Observaciones adicionales (opcional)...' }}"></textarea>
                            @if ($packagingStatus === 'rejected')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                    * El motivo del rechazo es requerido
                                </p>
                            @endif
                            @error('packagingComments')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closePackagingModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="savePackagingStatus"
                            class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors">
                            Guardar Cambios
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
                            <button wire:click="closeQualityModal" class="text-white hover:text-teal-200">
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
                                    <span class="ml-2 text-blue-600 dark:text-blue-400 font-medium">
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
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pz Buenas Prod.</div>
                                    <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($qualProductionGoodPieces) }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Ya Verificadas</div>
                                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ number_format($qualAlreadyWeighed) }}</div>
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
                                                <th class="px-3 py-2 text-center text-gray-600 dark:text-gray-400">Retrabajo</th>
                                                <th class="px-3 py-2 text-left text-gray-600 dark:text-gray-400">Por</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($qualWeighingsList as $qw)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $qw['weighed_at'] }}</td>
                                                    <td class="px-3 py-2 text-right font-medium text-green-600 dark:text-green-400">{{ number_format($qw['good_pieces']) }}</td>
                                                    <td class="px-3 py-2 text-right font-medium text-red-600 dark:text-red-400">{{ number_format($qw['bad_pieces']) }}</td>
                                                    <td class="px-3 py-2 text-center">
                                                        @if ($qw['bad_pieces'] > 0)
                                                            @php
                                                                $reworkLabel = match ($qw['rework_status']) {
                                                                    'pending_rework' => 'Pendiente',
                                                                    'in_rework' => 'En Proceso',
                                                                    'rework_complete' => 'Completado',
                                                                    default => '-',
                                                                };
                                                                $reworkColor = match ($qw['rework_status']) {
                                                                    'pending_rework' => 'text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20',
                                                                    'in_rework' => 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20',
                                                                    'rework_complete' => 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20',
                                                                    default => 'text-gray-500',
                                                                };
                                                            @endphp
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $reworkColor }}">
                                                                {{ $reworkLabel }}
                                                            </span>
                                                        @else
                                                            <span class="text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $qw['weighed_by'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- Formulario de nueva pesada (solo si hay piezas pendientes) --}}
                        @if ($qualRemainingPieces > 0)
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-5">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Nueva Pesada de Calidad</h4>

                                {{-- Pendiente de verificar --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pendiente de Verificar</label>
                                    <div class="w-full px-3 py-2 border rounded-lg font-bold text-lg text-center
                                        border-teal-300 dark:border-teal-600 bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300">
                                        {{ number_format($qualRemainingPieces) }} piezas
                                    </div>
                                </div>

                                {{-- Kit (opcional) --}}
                                @if (count($qualKits) > 0)
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kit (opcional)</label>
                                        <select wire:model="qualKitId"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                            <option value="">Sin kit</option>
                                            @foreach ($qualKits as $kit)
                                                <option value="{{ $kit->id }}">{{ $kit->kit_number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

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

                                {{-- Info de retrabajo --}}
                                @if ($qualBadPieces > 0)
                                    <div class="mb-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-3 rounded-lg">
                                        <div class="flex items-center text-sm text-yellow-700 dark:text-yellow-300">
                                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            Las {{ number_format($qualBadPieces) }} piezas rechazadas seran enviadas a Produccion para retrabajo.
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
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cerrar
                        </button>
                        @if ($qualRemainingPieces > 0)
                            <button wire:click="saveQuality"
                                class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-colors">
                                Registrar Pesada
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
                            <button wire:click="closeProductionModal" class="text-white hover:text-indigo-200">
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
                                    <span class="ml-2 text-blue-600 dark:text-blue-400 font-medium">
                                        {{ $selectedLotForProduction->lot_number }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Kit (opcional) --}}
                        @if (count($prodKits) > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kit (opcional)</label>
                                <select wire:model="prodKitId"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Sin kit</option>
                                    @foreach ($prodKits as $kit)
                                        <option value="{{ $kit->id }}">{{ $kit->kit_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

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

                        {{-- Piezas buenas y malas --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Buenas *</label>
                                <input wire:model="prodGoodPieces" type="number" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    placeholder="0">
                                @error('prodGoodPieces')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Malas *</label>
                                <input wire:model="prodBadPieces" type="number" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    placeholder="0">
                                @error('prodBadPieces')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
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
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="saveProduction"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                            Registrar Pesada
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
