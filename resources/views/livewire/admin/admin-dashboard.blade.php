<div class="space-y-6">

    {{-- ── Encabezado ───────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Panel de Administración</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ now()->format('l, d \d\e F \d\e Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                Sistema Activo
            </span>
        </div>
    </div>

    {{-- ── KPIs principales ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Work Orders --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <a href="{{ route('admin.work-orders.index') }}" class="text-xs text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Ver →</a>
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalWO) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Work Orders</div>
        </div>

        {{-- Purchase Orders --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <a href="{{ route('admin.purchase-orders.index') }}" class="text-xs text-purple-500 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">Ver →</a>
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalPO) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Purchase Orders</div>
        </div>

        {{-- Partes --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <a href="{{ route('admin.parts.index') }}" class="text-xs text-teal-500 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300">Ver →</a>
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalParts) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Partes Registradas</div>
        </div>

        {{-- Usuarios --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <a href="{{ route('admin.users.index') }}" class="text-xs text-orange-500 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300">Ver →</a>
            </div>
            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalUsers) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Usuarios del Sistema</div>
        </div>
    </div>

    {{-- ── Pipeline de Listas Preliminares ─────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Pipeline – Listas Preliminares</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Distribución por departamento activo</p>
            </div>
            <a href="{{ route('admin.sent-lists.index') }}"
                class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                Ver todas →
            </a>
        </div>
        <div class="grid grid-cols-5 divide-x divide-gray-200 dark:divide-gray-700">
            @php
                $pipelineColors = [
                    'blue'   => ['bg' => 'bg-blue-500',   'light' => 'bg-blue-50 dark:bg-blue-900/20',   'text' => 'text-blue-700 dark:text-blue-300',   'dot' => 'bg-blue-500'],
                    'yellow' => ['bg' => 'bg-yellow-500', 'light' => 'bg-yellow-50 dark:bg-yellow-900/20', 'text' => 'text-yellow-700 dark:text-yellow-300', 'dot' => 'bg-yellow-500'],
                    'indigo' => ['bg' => 'bg-indigo-500', 'light' => 'bg-indigo-50 dark:bg-indigo-900/20', 'text' => 'text-indigo-700 dark:text-indigo-300', 'dot' => 'bg-indigo-500'],
                    'green'  => ['bg' => 'bg-green-500',  'light' => 'bg-green-50 dark:bg-green-900/20',  'text' => 'text-green-700 dark:text-green-300',  'dot' => 'bg-green-500'],
                    'orange' => ['bg' => 'bg-orange-500', 'light' => 'bg-orange-50 dark:bg-orange-900/20', 'text' => 'text-orange-700 dark:text-orange-300', 'dot' => 'bg-orange-500'],
                ];
            @endphp
            @foreach ($pipeline as $dept => $info)
                @php $c = $pipelineColors[$info['color']]; @endphp
                <div class="flex flex-col items-center py-5 px-3 {{ $info['count'] > 0 ? $c['light'] : '' }}">
                    <div class="w-10 h-10 rounded-full {{ $info['count'] > 0 ? $c['bg'] : 'bg-gray-200 dark:bg-gray-700' }} flex items-center justify-center mb-2">
                        <span class="text-lg font-bold {{ $info['count'] > 0 ? 'text-white' : 'text-gray-400 dark:text-gray-500' }}">{{ $info['count'] }}</span>
                    </div>
                    <div class="text-xs font-medium {{ $info['count'] > 0 ? $c['text'] : 'text-gray-500 dark:text-gray-400' }} text-center">
                        {{ $info['label'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Fila: Lotes + Kits ──────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Lotes por estado --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Lotes</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ number_format($totalLots) }} total</p>
                </div>
                <a href="{{ route('admin.lots.index') }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Ver todos →</a>
            </div>
            <div class="p-5 space-y-3">
                @php
                    $lotStatuses = [
                        'pending'     => ['Pendiente',   'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300', 'bg-yellow-500'],
                        'in_progress' => ['En Proceso',  'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',         'bg-blue-500'],
                        'completed'   => ['Completado',  'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',      'bg-green-500'],
                        'cancelled'   => ['Cancelado',   'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',             'bg-red-500'],
                    ];
                @endphp
                @foreach ($lotStatuses as $key => [$label, $badge, $bar])
                    @php
                        $count = (int) ($lotsByStatus[$key] ?? 0);
                        $pct   = $totalLots > 0 ? round(($count / $totalLots) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-24 text-xs font-medium text-gray-600 dark:text-gray-400 shrink-0">{{ $label }}</span>
                        <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="{{ $bar }} h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="w-8 text-xs font-semibold text-gray-700 dark:text-gray-300 text-right">{{ $count }}</span>
                        <span class="w-8 text-xs text-gray-400 dark:text-gray-500 text-right">{{ $pct }}%</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Kits por estado --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Kits (CRIMP)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ number_format($totalKits) }} total</p>
                </div>
                <a href="{{ route('admin.kits.index') }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Ver todos →</a>
            </div>
            <div class="p-5 space-y-3">
                @php
                    $kitStatuses = [
                        'preparing'   => ['Preparando',   'bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300',         'bg-gray-400'],
                        'ready'       => ['Listo',         'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300', 'bg-yellow-500'],
                        'released'    => ['Liberado',      'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300', 'bg-indigo-500'],
                        'in_assembly' => ['En Ensamble',   'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',         'bg-blue-500'],
                        'rejected'    => ['Rechazado',     'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',             'bg-red-500'],
                    ];
                @endphp
                @foreach ($kitStatuses as $key => [$label, $badge, $bar])
                    @php
                        $count = (int) ($kitsByStatus[$key] ?? 0);
                        $pct   = $totalKits > 0 ? round(($count / $totalKits) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-24 text-xs font-medium text-gray-600 dark:text-gray-400 shrink-0">{{ $label }}</span>
                        <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="{{ $bar }} h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="w-8 text-xs font-semibold text-gray-700 dark:text-gray-300 text-right">{{ $count }}</span>
                        <span class="w-8 text-xs text-gray-400 dark:text-gray-500 text-right">{{ $pct }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Work Orders recientes ────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Work Orders Recientes</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Últimos 8 registros</p>
            </div>
            <a href="{{ route('admin.work-orders.index') }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Ver todos →</a>
        </div>
        @if ($recentWorkOrders->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/30">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">WO</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Parte</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($recentWorkOrders as $wo)
                            @php
                                $statusColor = match(optional($wo->status)->name ?? '') {
                                    'Abierto'    => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
                                    'En Proceso' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300',
                                    'Completado' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
                                    'Cancelado'  => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
                                    default      => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-5 py-3 font-mono font-semibold text-blue-600 dark:text-blue-400 whitespace-nowrap">
                                    {{ $wo->purchaseOrder->wo ?? $wo->wo_number }}
                                </td>
                                <td class="px-5 py-3 font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                    {{ $wo->purchaseOrder->part->number ?? '-' }}
                                    @if ($wo->purchaseOrder->part->is_crimp ?? false)
                                        <span class="ml-1 px-1 py-0.5 text-xs bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 rounded">CRIMP</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ $wo->purchaseOrder->part->description ?? '-' }}
                                </td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    {{ number_format($wo->purchaseOrder->quantity ?? 0) }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColor }}">
                                        {{ optional($wo->status)->name ?? 'Sin estado' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500 italic">
                No hay Work Orders registrados.
            </div>
        @endif
    </div>

    {{-- ── Listas Preliminares recientes ──────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Listas Preliminares Recientes</h2>
            </div>
            <a href="{{ route('admin.sent-lists.index') }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Ver todas →</a>
        </div>
        @if ($sentLists->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/30">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">WO(s)</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Departamento</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($sentLists as $sl)
                            @php
                                $wos = $sl->getEffectiveWorkOrders();

                                $deptLabel = match($sl->current_department) {
                                    'materiales' => ['Materiales',  'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'],
                                    'inspeccion' => ['Inspección',  'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300'],
                                    'produccion' => ['Producción',  'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'],
                                    'calidad'    => ['Calidad',     'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'],
                                    'envios'     => ['Empaque',     'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300'],
                                    default      => [$sl->current_department, 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'],
                                };

                                $statusLabel = match($sl->status) {
                                    'pending'   => ['Pendiente',   'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300'],
                                    'confirmed' => ['Confirmada',  'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'],
                                    'canceled'  => ['Cancelada',   'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'],
                                    default     => [$sl->status,   'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'],
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-5 py-3 font-mono font-semibold text-gray-800 dark:text-gray-200">
                                    #{{ $sl->id }}
                                </td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">
                                    @foreach ($wos->take(2) as $wo)
                                        <span class="inline-block font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded mr-1">
                                            {{ $wo->purchaseOrder->wo ?? $wo->wo_number }}
                                        </span>
                                    @endforeach
                                    @if ($wos->count() > 2)
                                        <span class="text-xs text-gray-400">+{{ $wos->count() - 2 }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $deptLabel[1] }}">
                                        {{ $deptLabel[0] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusLabel[1] }}">
                                        {{ $statusLabel[0] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                    {{ $sl->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <a href="{{ route('admin.sent-lists.show', $sl->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500 italic">
                No hay listas preliminares registradas.
            </div>
        @endif
    </div>

    {{-- ── Accesos rápidos ─────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Accesos Rápidos</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @php
                $shortcuts = [
                    ['route' => 'admin.production.index',  'label' => 'Producción',  'color' => 'indigo', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['route' => 'admin.materials.index',   'label' => 'Materiales',  'color' => 'blue',   'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
                    ['route' => 'admin.quality.index',     'label' => 'Calidad',     'color' => 'green',  'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['route' => 'admin.packaging.index',   'label' => 'Empaque',     'color' => 'orange', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    ['route' => 'admin.sent-lists.display','label' => 'Lista Envío', 'color' => 'teal',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                    ['route' => 'admin.capacity.wizard',   'label' => 'Capacidad',   'color' => 'purple', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                ];
                $shortcutColors = [
                    'indigo' => 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 border-indigo-200 dark:border-indigo-700',
                    'blue'   => 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40 border-blue-200 dark:border-blue-700',
                    'green'  => 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/40 border-green-200 dark:border-green-700',
                    'orange' => 'bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40 border-orange-200 dark:border-orange-700',
                    'teal'   => 'bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-900/40 border-teal-200 dark:border-teal-700',
                    'purple' => 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/40 border-purple-200 dark:border-purple-700',
                ];
            @endphp
            @foreach ($shortcuts as $s)
                <a href="{{ route($s['route']) }}" wire:navigate
                    class="flex flex-col items-center gap-2 p-4 rounded-xl border transition-colors {{ $shortcutColors[$s['color']] }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $s['icon'] }}"/>
                    </svg>
                    <span class="text-xs font-semibold text-center">{{ $s['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

</div>
