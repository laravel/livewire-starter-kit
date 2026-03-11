<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Área de Empaques</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Control y seguimiento de empaque de lotes</p>
        </div>
    </div>

    <!-- Pending Sent Lists -->
    @include('livewire.admin.sent-lists.partials.pending-lists-panel', [
        'pendingSentLists' => $pendingSentLists,
        'deptLabel'        => 'Empaques',
        'deptColor'        => 'green',
    ])

    <!-- Resumen de Empaques -->
    <section>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Resumen de Empaques
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- Pendientes de Empaque -->
            <div class="bg-white dark:bg-gray-800 border-2 border-yellow-200 dark:border-yellow-700 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mb-1">Pendientes</p>
                        <p class="text-2xl font-semibold text-yellow-700 dark:text-yellow-300">{{ number_format($lotsPendingPackaging) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-200 dark:border-yellow-700 flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Lotes listos para empacar</p>
            </div>

            <!-- En Proceso -->
            <div class="bg-white dark:bg-gray-800 border-2 border-blue-200 dark:border-blue-700 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">En Proceso</p>
                        <p class="text-2xl font-semibold text-blue-700 dark:text-blue-300">{{ number_format($lotsWithPackaging) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-700 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Lotes con registros de empaque</p>
            </div>

            <!-- Pendiente Decisión -->
            <div class="bg-white dark:bg-gray-800 border-2 border-purple-200 dark:border-purple-700 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-purple-600 dark:text-purple-400 mb-1">Pend. Decisión</p>
                        <p class="text-2xl font-semibold text-purple-700 dark:text-purple-300">{{ number_format($lotsPendingDecision) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-purple-50 dark:bg-purple-900/20 border-2 border-purple-200 dark:border-purple-700 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Viajero recibido, sin decisión</p>
            </div>

            <!-- Completados -->
            <div class="bg-white dark:bg-gray-800 border-2 border-green-200 dark:border-green-700 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-green-600 dark:text-green-400 mb-1">Completados</p>
                        <p class="text-2xl font-semibold text-green-700 dark:text-green-300">{{ number_format($lotsCompleted) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-700 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Empaque aprobado</p>
            </div>
        </div>
    </section>

    <!-- Estadísticas -->
    <section>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Estadísticas
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-5">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Registros</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalRecords) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 border-2 border-indigo-200 dark:border-indigo-700 rounded-lg p-5">
                <p class="text-xs text-indigo-600 dark:text-indigo-400 mb-1">Piezas Empacadas</p>
                <p class="text-2xl font-semibold text-indigo-700 dark:text-indigo-300">{{ number_format($totalPackedPieces) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 border-2 border-orange-200 dark:border-orange-700 rounded-lg p-5">
                <p class="text-xs text-orange-600 dark:text-orange-400 mb-1">Piezas Sobrantes</p>
                <p class="text-2xl font-semibold text-orange-700 dark:text-orange-300">{{ number_format($totalSurplusPieces) }}</p>
            </div>
        </div>
    </section>

    <!-- Lotes en Proceso de Empaque -->
    <section>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Lotes en Proceso de Empaque
        </h2>
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            @if ($lotsInProgress->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Lote</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">WO</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Parte</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Cantidad</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Empacadas</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Sobrantes</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Viajero</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Decisión</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($lotsInProgress as $lot)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-indigo-600 dark:text-indigo-400">{{ $lot->lot_number }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $lot->workOrder->purchaseOrder->wo ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $lot->workOrder->purchaseOrder->part->number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-right text-gray-900 dark:text-white font-medium">{{ number_format($lot->quantity) }}</td>
                                    <td class="px-6 py-4 text-right text-green-600 dark:text-green-400 font-medium">{{ number_format($lot->getPackagingPackedPieces()) }}</td>
                                    <td class="px-6 py-4 text-right text-orange-600 dark:text-orange-400 font-medium">{{ number_format($lot->getPackagingTotalSurplus()) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($lot->viajero_received)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-2 border-blue-200 dark:border-blue-700">Recibido</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 border-2 border-gray-200 dark:border-gray-600">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($lot->closure_decision)
                                            @php
                                                $decLabel = match ($lot->closure_decision) {
                                                    'complete_lot' => ['Completar', 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300 border-2 border-indigo-200 dark:border-indigo-700'],
                                                    'new_lot' => ['Nuevo Lote', 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border-2 border-green-200 dark:border-green-700'],
                                                    'close_as_is' => ['Cerrado', 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 border-2 border-orange-200 dark:border-orange-700'],
                                                    default => [$lot->closure_decision, 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border-2 border-gray-200 dark:border-gray-600'],
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $decLabel[1] }}">{{ $decLabel[0] }}</span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="mt-4 text-base font-medium text-gray-900 dark:text-white">No hay lotes en proceso de empaque</p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Los lotes aparecerán aquí cuando tengan registros de empaque</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Registros Recientes de Empaque -->
    <section>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Registros Recientes de Empaque
        </h2>
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            @if ($recentRecords->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Lote</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Parte</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Empacadas</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Sobrantes</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Empacó</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($recentRecords as $record)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-indigo-600 dark:text-indigo-400">{{ $record->lot->lot_number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $record->lot->workOrder->purchaseOrder->part->number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-right text-green-600 dark:text-green-400 font-medium">{{ number_format($record->packed_pieces) }}</td>
                                    <td class="px-6 py-4 text-right text-orange-600 dark:text-orange-400 font-medium">{{ number_format($record->effective_surplus) }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $record->packedBy->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $record->packed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-4 text-base font-medium text-gray-900 dark:text-white">No hay registros de empaque aún</p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Los registros aparecerán aquí cuando se creen</p>
                </div>
            @endif
        </div>
    </section>
</div>
