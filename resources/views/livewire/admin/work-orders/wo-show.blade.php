<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-300 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $workOrder->wo_number }}</p>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        WO: {{ $workOrder->purchaseOrder?->wo ?? $workOrder->wo_number }}
                    </h1>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        {{ $workOrder->purchaseOrder?->part?->number ?? '' }} — {{ $workOrder->purchaseOrder?->part?->description ?? '' }}
                    </p>
                </div>
                <div class="mt-3 sm:mt-0 flex space-x-2">
                    <a href="{{ route('admin.work-orders.index') }}" wire:navigate
                        class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Volver
                    </a>
                    <a href="{{ route('admin.work-orders.edit', $workOrder) }}" wire:navigate
                        class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                    </a>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        @php
            $progress = $workOrder->original_quantity > 0 ? round(($workOrder->sent_pieces / $workOrder->original_quantity) * 100, 1) : 0;
            $lotsCount = $workOrder->lots->count();
            $kitsCount = $workOrder->kits->count();
            $totalWeighings = $workOrder->lots->sum(fn($l) => $l->weighings->count());
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Estado</p>
                <span class="mt-1 inline-block px-2 py-0.5 text-xs font-semibold rounded-full text-white" style="background-color: {{ $workOrder->status?->color ?? '#6b7280' }}">
                    {{ $workOrder->status?->name ?? 'Sin estado' }}
                </span>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Cantidad</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($workOrder->original_quantity) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Lotes</p>
                <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $lotsCount }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Kits</p>
                <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $kitsCount }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Pesadas</p>
                <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $totalWeighings }}</p>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                @foreach([
                    'general' => ['label' => 'General', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'lots' => ['label' => 'Lotes (' . $lotsCount . ')', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                    'kits' => ['label' => 'Kits (' . $kitsCount . ')', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    'weighings' => ['label' => 'Pesadas (' . $totalWeighings . ')', 'icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3'],
                ] as $tab => $info)
                    <button wire:click="setTab('{{ $tab }}')"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-1.5
                        {{ $activeTab === $tab
                            ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $info['icon'] }}"/></svg>
                        {{ $info['label'] }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- ============================================ --}}
        {{-- TAB: GENERAL --}}
        {{-- ============================================ --}}
        @if($activeTab === 'general')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información General</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ID (Interno)</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $workOrder->wo_number }}</dd>
                    </div>
                    @if($workOrder->purchaseOrder?->wo)
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 p-3 rounded-lg -m-1">
                        <dt class="text-sm font-medium text-indigo-600 dark:text-indigo-400">WO (Cliente)</dt>
                        <dd class="mt-1 text-xl text-indigo-700 dark:text-indigo-300 font-bold">{{ $workOrder->purchaseOrder->wo }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full text-white" style="background-color: {{ $workOrder->status?->color ?? '#6b7280' }}">
                                {{ $workOrder->status?->name ?? 'Sin estado' }}
                            </span>
                        </dd>
                    </div>
                    @if($workOrder->purchaseOrder)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Purchase Order</dt>
                        <dd class="mt-1 text-sm"><a href="{{ route('admin.purchase-orders.show', $workOrder->purchaseOrder) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">{{ $workOrder->purchaseOrder->po_number }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parte</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->purchaseOrder->part->number ?? 'N/A' }} @if($workOrder->purchaseOrder->part)<span class="text-gray-500 dark:text-gray-400">- {{ $workOrder->purchaseOrder->part->description }}</span>@endif</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cantidad Original</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ number_format($workOrder->original_quantity) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Piezas Enviadas</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ number_format($workOrder->sent_pieces) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cantidad Pendiente</dt>
                        <dd class="mt-1 text-sm {{ $workOrder->pending_quantity > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }} font-semibold">{{ number_format($workOrder->pending_quantity) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Apertura</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->opened_date?->format('d/m/Y') ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Prog. Envío</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->scheduled_send_date?->format('d/m/Y') ?? 'No definida' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Real Envío</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->actual_send_date?->format('d/m/Y') ?? 'No enviado' }}</dd>
                    </div>
                    @if($workOrder->eq)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Equipo (EQ)</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->eq }}</dd>
                    </div>
                    @endif
                    @if($workOrder->pr)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Personal (PR)</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->pr }}</dd>
                    </div>
                    @endif
                </dl>
                @if($workOrder->comments)
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Comentarios</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->comments }}</dd>
                </div>
                @endif
            </div>

            <div class="space-y-6">
                {{-- Progress --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Progreso</h2>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Completado</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                        <div class="h-3 rounded-full {{ $progress >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ min($progress, 100) }}%"></div>
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($workOrder->sent_pieces) }} / {{ number_format($workOrder->original_quantity) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">piezas enviadas</p>
                    </div>
                </div>
                {{-- Status Log --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Historial de Estados</h2>
                    @if($workOrder->statusLogs->count() > 0)
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($workOrder->statusLogs->sortByDesc('created_at')->take(5) as $log)
                            <li>
                                <div class="relative pb-6">
                                    @if(!$loop->last)<span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>@endif
                                    <div class="relative flex space-x-3">
                                        <div><span class="h-8 w-8 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-gray-800" style="background-color: {{ $log->toStatus->color ?? '#6B7280' }}"><svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span></div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    @if($log->fromStatus)<span class="font-medium" style="color: {{ $log->fromStatus->color }}">{{ $log->fromStatus->name }}</span> → @else Creado como @endif
                                                    <span class="font-medium" style="color: {{ $log->toStatus->color }}">{{ $log->toStatus->name }}</span>
                                                    @if($log->user) por <span class="font-medium text-gray-900 dark:text-white">{{ $log->user->name }}</span>@endif
                                                </p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sin cambios de estado.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Signatures --}}
        @if($workOrder->purchaseOrder?->pdf_path)
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Firmas del Documento</h2>
                <button wire:click="openSignatureModal" class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Firmar
                </button>
            </div>
            @if($signatures && (is_countable($signatures) ? count($signatures) : $signatures->count()) > 0)
                <div class="space-y-3">
                    @foreach($signatures as $signature)
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <img src="{{ $signature->signature_url }}" alt="Firma" class="h-12 w-20 object-contain border rounded bg-white">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $signature->user->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $signature->signed_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Firmado</span>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Este documento aún no ha sido firmado.</p>
            @endif
        </div>
        @endif
        @endif

        {{-- ============================================ --}}
        {{-- TAB: LOTES --}}
        {{-- ============================================ --}}
        @if($activeTab === 'lots')
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lotes de esta Work Order</h2>
                <button wire:click="openCreateLotModal"
                    class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar Lote
                </button>
            </div>
            @if($workOrder->lots->isEmpty())
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No hay lotes creados. Haz clic en "Agregar Lote" para crear uno.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lote #</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kits</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pesadas</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($workOrder->lots as $lot)
                            @php
                                $lotColor = match($lot->status) {
                                    'pending' => 'zinc', 'in_progress' => 'yellow', 'completed' => 'green', 'cancelled' => 'red', default => 'zinc',
                                };
                                $lotWeighed = $lot->weighings->sum('good_pieces') + $lot->weighings->sum('bad_pieces');
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $lot->lot_number }}</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900 dark:text-white">
                                    {{ number_format($lot->quantity) }}
                                    @if($lotWeighed > 0)
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Pesadas: {{ number_format($lotWeighed) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center"><flux:badge :color="$lotColor" size="sm">{{ ucfirst(str_replace('_', ' ', $lot->status)) }}</flux:badge></td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ $lot->kits->count() }}</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ $lot->weighings->count() }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <button wire:click="openCreateWeighingModal({{ $lot->id }})" class="p-1.5 text-purple-600 hover:text-purple-800 dark:text-purple-400" title="Registrar pesada">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                        </button>
                                        <button wire:click="openEditLotModal({{ $lot->id }})" class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400" title="Editar lote">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if($lot->canBeDeleted())
                                        <button wire:click="confirmDeleteLot({{ $lot->id }})" class="p-1.5 text-red-600 hover:text-red-800 dark:text-red-400" title="Eliminar lote">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- TAB: KITS --}}
        {{-- ============================================ --}}
        @if($activeTab === 'kits')
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Kits de esta Work Order</h2>
                @if($workOrder->lots->isNotEmpty())
                <button wire:click="openCreateKitModal"
                    class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar Kit
                </button>
                @else
                <span class="text-xs text-gray-500 dark:text-gray-400 italic">Crea al menos un lote primero</span>
                @endif
            </div>
            @if($workOrder->kits->isEmpty())
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No hay kits creados. {{ $workOrder->lots->isEmpty() ? 'Primero crea un lote.' : 'Haz clic en "Agregar Kit" para crear uno.' }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kit #</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lotes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Preparado por</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Liberado por</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($workOrder->kits as $kit)
                            @php
                                $kitColor = match($kit->status) {
                                    'preparing' => 'yellow', 'ready' => 'blue', 'released' => 'green', 'in_assembly' => 'orange', 'rejected' => 'red', default => 'zinc',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $kit->kit_number }}</td>
                                <td class="px-6 py-4 text-center"><flux:badge :color="$kitColor" size="sm">{{ $kit->status_label ?? ucfirst($kit->status) }}</flux:badge></td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $kit->lots->pluck('lot_number')->join(', ') ?: 'Sin lotes' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $kit->preparedBy?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $kit->releasedBy?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <button wire:click="openEditKitModal({{ $kit->id }})" class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400" title="Editar kit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if($kit->canBeDeleted())
                                        <button wire:click="confirmDeleteKit({{ $kit->id }})" class="p-1.5 text-red-600 hover:text-red-800 dark:text-red-400" title="Eliminar kit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- TAB: PESADAS --}}
        {{-- ============================================ --}}
        @if($activeTab === 'weighings')
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pesadas de esta Work Order</h2>
                @if($workOrder->lots->isNotEmpty())
                <button wire:click="openCreateWeighingModal"
                    class="inline-flex items-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Registrar Pesada
                </button>
                @else
                <span class="text-xs text-gray-500 dark:text-gray-400 italic">Crea al menos un lote primero</span>
                @endif
            </div>
            @if($weighings->isEmpty())
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No hay pesadas registradas.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lote</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kit</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Buenas</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Malas</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pesó</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($weighings as $weighing)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $weighing->weighed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $weighing->lot?->lot_number ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $weighing->kit?->kit_number ?? '—' }}</td>
                                <td class="px-6 py-4 text-center text-sm font-medium text-green-600 dark:text-green-400">{{ number_format($weighing->good_pieces) }}</td>
                                <td class="px-6 py-4 text-center text-sm font-medium text-red-600 dark:text-red-400">{{ number_format($weighing->bad_pieces) }}</td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-gray-900 dark:text-white">{{ number_format($weighing->good_pieces + $weighing->bad_pieces) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $weighing->weighedBy?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <button wire:click="openEditWeighingModal({{ $weighing->id }})" class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400" title="Editar pesada">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="confirmDeleteWeighing({{ $weighing->id }})" class="p-1.5 text-red-600 hover:text-red-800 dark:text-red-400" title="Eliminar pesada">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @endif

    </div>

    {{-- ============================================ --}}
    {{-- MODALS --}}
    {{-- ============================================ --}}

    {{-- Lot Modal (Create/Edit) --}}
    @if($showLotModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeLotModal"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                <form wire:submit="saveLot">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ $editingLotId ? 'Editar Lote' : 'Agregar Lote' }}</h3>
                        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                <span class="font-medium">WO:</span> {{ $workOrder->purchaseOrder?->wo ?? 'N/A' }} |
                                <span class="font-medium">Cant. WO:</span> {{ number_format($workOrder->original_quantity) }}
                            </p>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Lote *</label>
                                <input type="text" wire:model="lotNumber" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: 001">
                                @error('lotNumber') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad *</label>
                                <input type="number" wire:model="lotQuantity" min="1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('lotQuantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            @if($editingLotId)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                                <select wire:model="lotStatus" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="pending">Pendiente</option>
                                    <option value="in_progress">En Progreso</option>
                                    <option value="completed">Completado</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                                <input type="text" wire:model="lotDescription" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Comentarios</label>
                                <textarea wire:model="lotComments" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end gap-2">
                        <button type="button" wire:click="closeLotModal" class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">{{ $editingLotId ? 'Guardar' : 'Crear Lote' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Lot Confirm --}}
    @if($showDeleteLotConfirm)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showDeleteLotConfirm', false)"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-sm w-full p-6">
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Eliminar Lote</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">¿Estás seguro? Se eliminarán también sus pesadas y se desasociarán los kits. Esta acción no se puede deshacer.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showDeleteLotConfirm', false)" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">Cancelar</button>
                    <button wire:click="deleteLot" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Kit Modal (Create/Edit) --}}
    @if($showKitModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeKitModal"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full">
                <form wire:submit="saveKit">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ $editingKitId ? 'Editar Kit' : 'Crear Kit' }}</h3>
                        <div class="space-y-4">
                            @if($editingKitId)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Kit</label>
                                @php $editKit = \App\Models\Kit::find($editingKitId); @endphp
                                <input type="text" disabled value="{{ $editKit?->kit_number ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm">
                            </div>
                            @else
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Kit</label>
                                <input type="text" disabled value="(Se generará automáticamente)" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 shadow-sm">
                            </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                                <select wire:model="kitStatus" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="preparing">En Preparación</option>
                                    <option value="ready">Listo</option>
                                    @if($editingKitId)
                                    <option value="released">Liberado</option>
                                    <option value="in_assembly">En Ensamble</option>
                                    <option value="rejected">Rechazado</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lotes a Incluir <span class="text-xs text-red-500">* (obligatorio)</span></label>
                                <div class="mt-1 space-y-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-3">
                                    @forelse($workOrder->lots as $lot)
                                        <label class="flex items-center space-x-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer">
                                            <input type="checkbox" wire:model="selectedLots" value="{{ $lot->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-900 dark:text-white">{{ $lot->lot_number }} — {{ number_format($lot->quantity) }} pcs ({{ ucfirst($lot->status) }})</span>
                                        </label>
                                    @empty
                                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No hay lotes disponibles.</p>
                                    @endforelse
                                </div>
                                @error('selectedLots') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notas de Validación</label>
                                <textarea wire:model="kitValidationNotes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end gap-2">
                        <button type="button" wire:click="closeKitModal" class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">{{ $editingKitId ? 'Guardar' : 'Crear Kit' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Kit Confirm --}}
    @if($showDeleteKitConfirm)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showDeleteKitConfirm', false)"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-sm w-full p-6">
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Eliminar Kit</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">¿Estás seguro? Esta acción no se puede deshacer.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showDeleteKitConfirm', false)" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">Cancelar</button>
                    <button wire:click="deleteKit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Weighing Modal (Create/Edit) --}}
    @if($showWeighingModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeWeighingModal"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full">
                <form wire:submit="saveWeighing">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ $editingWeighingId ? 'Editar Pesada' : 'Registrar Pesada' }}</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lote *</label>
                                <select wire:model.live="weighingLotId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">— Seleccionar Lote —</option>
                                    @foreach($workOrder->lots as $lot)
                                        <option value="{{ $lot->id }}">{{ $lot->lot_number }} — {{ number_format($lot->quantity) }} pcs</option>
                                    @endforeach
                                </select>
                                @error('weighingLotId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            @if($weighingLotId)
                            <div class="p-3 rounded-lg {{ $remainingPieces > 0 ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'bg-green-50 dark:bg-green-900/20' }}">
                                <div class="grid grid-cols-3 gap-2 text-center">
                                    @php $selectedLot = $workOrder->lots->find($weighingLotId); @endphp
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Cant. Lote</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($selectedLot?->quantity ?? 0) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Ya Pesadas</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format(($selectedLot?->quantity ?? 0) - $remainingPieces) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs {{ $remainingPieces > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-green-600 dark:text-green-400' }}">Pendiente</p>
                                        <p class="text-lg font-bold {{ $remainingPieces > 0 ? 'text-indigo-700 dark:text-indigo-300' : 'text-green-700 dark:text-green-300' }}">{{ number_format($remainingPieces) }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kit (opcional)</label>
                                <select wire:model="weighingKitId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">— Sin Kit —</option>
                                    @foreach($workOrder->kits as $kit)
                                        <option value="{{ $kit->id }}">{{ $kit->kit_number }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Piezas Buenas *</label>
                                    <input type="number" wire:model="goodPieces" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-green-500 focus:border-green-500">
                                    @error('goodPieces') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Piezas Malas *</label>
                                    <input type="number" wire:model="badPieces" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-red-500 focus:border-red-500">
                                    @error('badPieces') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha y Hora *</label>
                                <input type="datetime-local" wire:model="weighedAt" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('weighedAt') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Comentarios</label>
                                <textarea wire:model="weighingComments" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end gap-2">
                        <button type="button" wire:click="closeWeighingModal" class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg">{{ $editingWeighingId ? 'Guardar' : 'Registrar Pesada' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Weighing Confirm --}}
    @if($showDeleteWeighingConfirm)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showDeleteWeighingConfirm', false)"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-sm w-full p-6">
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Eliminar Pesada</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">¿Estás seguro? Esta acción no se puede deshacer.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showDeleteWeighingConfirm', false)" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">Cancelar</button>
                    <button wire:click="deleteWeighing" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Signature Modal Component --}}
    <livewire:admin.signature-modal @signature-completed="refreshWorkOrder" />
</div>
