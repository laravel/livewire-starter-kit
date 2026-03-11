{{--
    Panel reutilizable: "Mis listas pendientes"
    Variables esperadas:
      $pendingSentLists  — Collection de SentList
      $deptLabel         — string: nombre del departamento actual (para el título)
      $deptColor         — string: color Tailwind (blue, yellow, purple, green, orange)
--}}
@php
    $colorMap = [
        'blue'   => ['bg' => 'bg-blue-600',   'light' => 'bg-blue-50 dark:bg-blue-900/20',   'border' => 'border-blue-200 dark:border-blue-700',   'text' => 'text-blue-700 dark:text-blue-300',   'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
        'yellow' => ['bg' => 'bg-yellow-600',  'light' => 'bg-yellow-50 dark:bg-yellow-900/20', 'border' => 'border-yellow-200 dark:border-yellow-700', 'text' => 'text-yellow-700 dark:text-yellow-300', 'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300'],
        'purple' => ['bg' => 'bg-purple-600',  'light' => 'bg-purple-50 dark:bg-purple-900/20', 'border' => 'border-purple-200 dark:border-purple-700', 'text' => 'text-purple-700 dark:text-purple-300', 'badge' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'],
        'green'  => ['bg' => 'bg-green-600',   'light' => 'bg-green-50 dark:bg-green-900/20',   'border' => 'border-green-200 dark:border-green-700',   'text' => 'text-green-700 dark:text-green-300',   'badge' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'],
        'orange' => ['bg' => 'bg-orange-600',  'light' => 'bg-orange-50 dark:bg-orange-900/20', 'border' => 'border-orange-200 dark:border-orange-700', 'text' => 'text-orange-700 dark:text-orange-300', 'badge' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300'],
    ];
    $c = $colorMap[$deptColor ?? 'blue'];
@endphp

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">

    {{-- Panel Header --}}
    <div class="flex items-center justify-between px-5 py-4 {{ $c['light'] }} border-b {{ $c['border'] }}">
        <div class="flex items-center gap-3">
            <div class="w-2.5 h-2.5 rounded-full {{ $c['bg'] }} animate-pulse"></div>
            <h2 class="text-base font-semibold {{ $c['text'] }}">
                Listas en espera — {{ $deptLabel }}
            </h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $c['badge'] }}">
                {{ $pendingSentLists->count() }}
            </span>
        </div>
    </div>

    @if ($pendingSentLists->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sin listas pendientes</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Cuando llegue una lista aquí aparecerá.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($pendingSentLists as $sl)
                @php
                    $totalLots    = $sl->workOrders->flatMap->lots->count();
                    $hasRejection = $sl->unresolvedRejections?->isNotEmpty() ?? false;
                    $deptLabel_sl = \App\Models\SentList::getDepartments()[$sl->current_department] ?? $sl->current_department;
                @endphp

                <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">

                    {{-- ID Badge --}}
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $c['light'] }} {{ $c['border'] }} border flex items-center justify-center">
                        <span class="text-xs font-bold {{ $c['text'] }}">#{{ $sl->id }}</span>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                Lista #{{ $sl->id }}
                            </span>
                            {{-- Rejection alert --}}
                            @if ($hasRejection)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    Rechazada — requiere corrección
                                </span>
                            @endif
                            {{-- Department badge --}}
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $c['badge'] }}">
                                {{ $deptLabel_sl }}
                            </span>
                        </div>

                        {{-- Parts list --}}
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate">
                            @foreach ($sl->workOrders->take(3) as $wo)
                                {{ $wo->purchaseOrder->part->number ?? '?' }}
                                ({{ number_format($wo->purchaseOrder->quantity ?? 0) }} pz){{ !$loop->last ? ' · ' : '' }}
                            @endforeach
                            @if ($sl->workOrders->count() > 3)
                                + {{ $sl->workOrders->count() - 3 }} más
                            @endif
                        </p>

                        {{-- Dates + lots --}}
                        <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-gray-400 dark:text-gray-500">
                            <span>
                                {{ $sl->start_date?->format('d/m/Y') }} – {{ $sl->end_date?->format('d/m/Y') }}
                            </span>
                            <span>{{ $totalLots }} {{ Str::plural('lote', $totalLots) }}</span>
                            <span>{{ $sl->workOrders->count() }} WOs</span>
                        </div>
                    </div>

                    {{-- Action button --}}
                    <a href="{{ route('admin.sent-lists.show', $sl) }}"
                       class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 {{ $c['bg'] }} hover:opacity-90 text-white text-sm font-medium rounded-lg transition-opacity shadow-sm">
                        Gestionar
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>
