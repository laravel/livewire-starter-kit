<div class="min-h-screen bg-gray-50 dark:bg-gray-900">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pesadas de Produccion</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Metricas de pesadas, rendimiento y actividad</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- ════════════════════════════════════════════ --}}
        {{-- SECCIÓN: Hoy --}}
        {{-- ════════════════════════════════════════════ --}}
        <section>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Actividad de Hoy
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Pesadas Hoy</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($todayWeighings) }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 rounded-xl p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-green-600 dark:text-green-400">Piezas Buenas Hoy</p>
                            <p class="text-3xl font-bold text-green-700 dark:text-green-300 mt-1">{{ number_format($todayGoodPieces) }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 rounded-xl p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-red-600 dark:text-red-400">Piezas Malas Hoy</p>
                            <p class="text-3xl font-bold text-red-700 dark:text-red-300 mt-1">{{ number_format($todayBadPieces) }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════ --}}
        {{-- SECCIÓN: Acceso Rápido --}}
        {{-- ════════════════════════════════════════════ --}}
        <section>
            <a href="{{ route('admin.production.weighings') }}" wire:navigate
                class="group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-lg transition-all block">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 transition-colors">
                        <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Gestionar Pesadas</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Crear, editar y eliminar registros de pesadas de produccion</p>
                        <div class="flex items-center gap-4 mt-4">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ number_format($totalWeighings) }} pesadas totales</span>
                            </div>
                            @if ($pendingReworkPieces > 0)
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                                    <span class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ number_format($pendingReworkPieces) }} pz retrabajo</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-colors flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        </section>

        {{-- ════════════════════════════════════════════ --}}
        {{-- SECCIÓN: Resumen General --}}
        {{-- ════════════════════════════════════════════ --}}
        <section>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Resumen General
            </h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Pesadas</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalWeighings) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 rounded-xl p-4 text-center">
                    <div class="text-sm text-green-600 dark:text-green-400">Piezas Buenas</div>
                    <div class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1">{{ number_format($totalGoodPieces) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 rounded-xl p-4 text-center">
                    <div class="text-sm text-red-600 dark:text-red-400">Piezas Malas</div>
                    <div class="text-2xl font-bold text-red-700 dark:text-red-300 mt-1">{{ number_format($totalBadPieces) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 border {{ $yieldRate >= 95 ? 'border-green-200 dark:border-green-800' : ($yieldRate >= 85 ? 'border-yellow-200 dark:border-yellow-800' : 'border-red-200 dark:border-red-800') }} rounded-xl p-4 text-center">
                    <div class="text-sm {{ $yieldRate >= 95 ? 'text-green-600 dark:text-green-400' : ($yieldRate >= 85 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">Rendimiento</div>
                    <div class="text-2xl font-bold {{ $yieldRate >= 95 ? 'text-green-700 dark:text-green-300' : ($yieldRate >= 85 ? 'text-yellow-700 dark:text-yellow-300' : 'text-red-700 dark:text-red-300') }} mt-1">{{ $yieldRate }}%</div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════ --}}
        {{-- SECCIÓN: Estado de Lotes --}}
        {{-- ════════════════════════════════════════════ --}}
        <section>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Estado de Lotes
            </h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Con Pesadas</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($lotsWithWeighings) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 rounded-xl p-4 text-center">
                    <div class="text-sm text-green-600 dark:text-green-400">Completados</div>
                    <div class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1">{{ number_format($lotsFullyWeighed) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 text-center">
                    <div class="text-sm text-yellow-600 dark:text-yellow-400">Pendientes</div>
                    <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-300 mt-1">{{ number_format($lotsPendingWeighing) }}</div>
                </div>
                @if ($reworkLots > 0)
                    <div class="bg-white dark:bg-gray-800 border border-orange-200 dark:border-orange-800 rounded-xl p-4 text-center">
                        <div class="text-sm text-orange-600 dark:text-orange-400">Con Retrabajo</div>
                        <div class="text-2xl font-bold text-orange-700 dark:text-orange-300 mt-1">{{ number_format($reworkLots) }}</div>
                        <div class="text-xs text-orange-500 dark:text-orange-400 mt-1">{{ number_format($pendingReworkPieces) }} piezas</div>
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-center">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Sin Pesar</div>
                        <div class="text-2xl font-bold text-gray-700 dark:text-gray-300 mt-1">{{ number_format($lotsWithoutWeighings) }}</div>
                    </div>
                @endif
            </div>
        </section>

        {{-- ════════════════════════════════════════════ --}}
        {{-- SECCIÓN: Top Operadores (30 días) --}}
        {{-- ════════════════════════════════════════════ --}}
        @if ($topOperators->count() > 0)
            <section>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Top Operadores (30 dias)
                </h2>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Operador</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Pesadas</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Piezas Buenas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($topOperators as $index => $op)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">{{ $op->weighedBy->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($op->total_weighings) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-green-600 dark:text-green-400">{{ number_format($op->total_good) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- ════════════════════════════════════════════ --}}
        {{-- SECCIÓN: Actividad Reciente --}}
        {{-- ════════════════════════════════════════════ --}}
        <section>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Actividad Reciente
            </h2>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                @if ($recentWeighings->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">WO</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Lote</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Parte</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Buenas</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-red-600 dark:text-red-400 uppercase">Malas</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Operador</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($recentWeighings as $w)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $w->weighed_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-blue-600 dark:text-blue-400 font-medium">{{ $w->lot->workOrder->purchaseOrder->wo ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">{{ $w->lot->lot_number ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $w->lot->workOrder->purchaseOrder->part->number ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-green-600 dark:text-green-400">{{ number_format($w->good_pieces) }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-red-600 dark:text-red-400">{{ number_format($w->bad_pieces) }}</td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $w->weighedBy->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('admin.production.weighings', ['search' => $w->lot->lot_number ?? '']) }}" wire:navigate
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors">
                                                Ver
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-4 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                        <p class="mt-4 text-base font-medium text-gray-900 dark:text-white">Sin pesadas registradas</p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Las pesadas apareceran aqui cuando se registren.</p>
                    </div>
                @endif
            </div>
        </section>

    </div>
</div>
