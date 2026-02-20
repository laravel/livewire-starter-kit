<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
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
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Area de Calidad</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Verificacion de pesadas de produccion</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total con Pesadas</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="text-sm text-yellow-600 dark:text-yellow-400">Pendientes</div>
                <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-300 mt-1">{{ $stats['pending'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="text-sm text-green-600 dark:text-green-400">Verificados</div>
                <div class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1">{{ $stats['completed'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="text-sm text-red-600 dark:text-red-400">Con Rechazos</div>
                <div class="text-2xl font-bold text-red-700 dark:text-red-300 mt-1">{{ $stats['rejected'] }}</div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input wire:model.live.debounce.300ms="search" type="text"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        placeholder="Buscar por WO, lote, parte...">
                </div>
                <select wire:model.live="filterQualityStatus"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Todos los estados</option>
                    <option value="pending">Pendientes</option>
                    <option value="completed">Verificados</option>
                    <option value="rejected">Con Rechazos</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">WO</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Lote</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Parte</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Cant. Lote</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Prod. Buenas</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-teal-600 dark:text-teal-400 uppercase">Cal. Aprobadas</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-red-600 dark:text-red-400 uppercase">Cal. Rechazadas</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-yellow-600 dark:text-yellow-400 uppercase">Pendientes</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Estado</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($lots as $lot)
                            @php
                                $wo = $lot->workOrder;
                                $po = $wo->purchaseOrder;
                                $part = $po->part;
                                $prodGood = $lot->getProductionGoodPieces();
                                $qualGood = $lot->getQualityGoodPieces();
                                $qualBad = $lot->getQualityBadPieces();
                                $qualPendingPcs = $lot->getQualityPendingPieces();
                                $semaphore = $lot->getQualitySemaphoreStatus();
                                $hasRejected = $qualBad > 0;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 text-blue-600 dark:text-blue-400 font-medium">{{ $po->wo }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">{{ $lot->lot_number }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $part->number }}</td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($lot->quantity) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-green-600 dark:text-green-400">{{ number_format($prodGood) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-teal-600 dark:text-teal-400">{{ number_format($qualGood) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-red-600 dark:text-red-400">{{ number_format($qualBad) }}</td>
                                <td class="px-4 py-3 text-right font-bold {{ $qualPendingPcs > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400' }}">
                                    {{ number_format($qualPendingPcs) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        @php
                                            $semColor = match ($semaphore) {
                                                'green' => 'bg-green-500',
                                                'yellow' => 'bg-yellow-400',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <span class="inline-block w-4 h-4 rounded {{ $semColor }}" title="Calidad: {{ ucfirst($semaphore) }}"></span>
                                        @if ($hasRejected)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20">
                                                Descarte
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="openDetailModal({{ $lot->id }})"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 rounded-lg hover:bg-teal-100 dark:hover:bg-teal-900/40 transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver Detalle
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="mt-4 text-base font-medium text-gray-900 dark:text-white">No hay lotes con pesadas de produccion</p>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Los lotes apareceran aqui cuando Produccion registre pesadas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($lots->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $lots->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de Detalle del Lote --}}
    @if ($showDetailModal && $selectedLot)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="detail-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeDetailModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-teal-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="detail-modal-title" class="text-lg font-semibold text-white">Detalle de Calidad - Lote</h3>
                                <p class="text-sm text-teal-100 mt-1">
                                    WO: {{ $selectedLot->workOrder->purchaseOrder->wo ?? 'N/A' }} |
                                    Lote: {{ $selectedLot->lot_number }} |
                                    Parte: {{ $selectedLot->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                </p>
                            </div>
                            <button wire:click="closeDetailModal" class="text-white hover:text-teal-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-6 max-h-[70vh] overflow-y-auto">
                        {{-- Resumen --}}
                        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                            <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg text-center">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Cant. Lote</div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($selectedLot->quantity) }}</div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg text-center">
                                <div class="text-xs text-green-600 dark:text-green-400">Prod. Buenas</div>
                                <div class="text-lg font-bold text-green-700 dark:text-green-300">{{ number_format($prodGoodTotal) }}</div>
                            </div>
                            <div class="bg-teal-50 dark:bg-teal-900/20 p-3 rounded-lg text-center">
                                <div class="text-xs text-teal-600 dark:text-teal-400">Cal. Aprobadas</div>
                                <div class="text-lg font-bold text-teal-700 dark:text-teal-300">{{ number_format($qualGoodTotal) }}</div>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg text-center">
                                <div class="text-xs text-red-600 dark:text-red-400">Cal. Rechazadas</div>
                                <div class="text-lg font-bold text-red-700 dark:text-red-300">{{ number_format($qualBadTotal) }}</div>
                            </div>
                            <div class="{{ $qualPending > 0 ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-green-50 dark:bg-green-900/20' }} p-3 rounded-lg text-center">
                                <div class="text-xs {{ $qualPending > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400' }}">Pendientes</div>
                                <div class="text-lg font-bold {{ $qualPending > 0 ? 'text-yellow-700 dark:text-yellow-300' : 'text-green-700 dark:text-green-300' }}">{{ number_format($qualPending) }}</div>
                            </div>
                        </div>

                        {{-- Pesadas de Produccion --}}
                        <div>
                            <h4 class="text-sm font-semibold text-indigo-700 dark:text-indigo-300 mb-2 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-indigo-500 inline-block"></span>
                                Pesadas de Produccion ({{ count($productionWeighings) }})
                            </h4>
                            @if (count($productionWeighings) > 0)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    <table class="w-full text-xs">
                                        <thead class="bg-indigo-50 dark:bg-indigo-900/20">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-gray-600 dark:text-gray-400">Fecha</th>
                                                <th class="px-3 py-2 text-right text-indigo-600 dark:text-indigo-400">Pz Pesadas</th>
                                                <th class="px-3 py-2 text-left text-gray-600 dark:text-gray-400">Por</th>
                                                <th class="px-3 py-2 text-left text-gray-600 dark:text-gray-400">Comentarios</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($productionWeighings as $pw)
                                                <tr>
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $pw['weighed_at'] }}</td>
                                                    <td class="px-3 py-2 text-right font-medium text-indigo-600 dark:text-indigo-400">{{ number_format($pw['good_pieces']) }}</td>
                                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $pw['weighed_by'] }}</td>
                                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ $pw['comments'] ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-indigo-50 dark:bg-indigo-900/20 font-semibold">
                                            <tr>
                                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Total</td>
                                                <td class="px-3 py-2 text-right text-indigo-600 dark:text-indigo-400">{{ number_format($prodGoodTotal) }}</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sin pesadas de produccion.</p>
                            @endif
                        </div>

                        {{-- Pesadas de Calidad --}}
                        <div>
                            <h4 class="text-sm font-semibold text-teal-700 dark:text-teal-300 mb-2 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-teal-500 inline-block"></span>
                                Pesadas de Calidad ({{ count($qualityWeighings) }})
                            </h4>
                            @if (count($qualityWeighings) > 0)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    <table class="w-full text-xs">
                                        <thead class="bg-teal-50 dark:bg-teal-900/20">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-gray-600 dark:text-gray-400">Fecha</th>
                                                <th class="px-3 py-2 text-right text-green-600 dark:text-green-400">Aprobadas</th>
                                                <th class="px-3 py-2 text-right text-red-600 dark:text-red-400">Rechazadas</th>
                                                <th class="px-3 py-2 text-left text-gray-600 dark:text-gray-400">Por</th>
                                                <th class="px-3 py-2 text-center text-gray-600 dark:text-gray-400">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($qualityWeighings as $qw)
                                                <tr>
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $qw['weighed_at'] }}</td>
                                                    <td class="px-3 py-2 text-right font-medium text-green-600 dark:text-green-400">{{ number_format($qw['good_pieces']) }}</td>
                                                    <td class="px-3 py-2 text-right font-medium text-red-600 dark:text-red-400">
                                                        {{ number_format($qw['bad_pieces']) }}
                                                        @if ($qw['bad_pieces'] > 0)
                                                            <span class="ml-1 text-gray-400 text-xs">(descarte)</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $qw['weighed_by'] }}</td>
                                                    <td class="px-3 py-2 text-center">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <button wire:click="editQualityWeighing({{ $qw['id'] }})"
                                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300" title="Editar">
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
                                        <tfoot class="bg-teal-50 dark:bg-teal-900/20 font-semibold">
                                            <tr>
                                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Total</td>
                                                <td class="px-3 py-2 text-right text-green-600 dark:text-green-400">{{ number_format($qualGoodTotal) }}</td>
                                                <td class="px-3 py-2 text-right text-red-600 dark:text-red-400">{{ number_format($qualBadTotal) }}</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sin pesadas de calidad registradas.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeDetailModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cerrar
                        </button>
                        @if ($qualPending > 0)
                            <button wire:click="openWeighingModal"
                                class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Nueva Pesada de Calidad
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Nueva Pesada de Calidad --}}
    @if ($showWeighingModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="weighing-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 transition-opacity" wire:click="closeWeighingModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 {{ $editingQualityWeighingId ? 'bg-blue-700' : 'bg-teal-700' }}">
                        <h3 id="weighing-modal-title" class="text-lg font-semibold text-white">
                            {{ $editingQualityWeighingId ? 'Editar Pesada de Calidad' : 'Nueva Pesada de Calidad' }}
                        </h3>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        {{-- Pendiente --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pendiente de Verificar</label>
                            <div class="w-full px-3 py-2 border rounded-lg font-bold text-lg text-center border-teal-300 dark:border-teal-600 bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300">
                                {{ number_format($qualRemainingPieces) }} piezas
                            </div>
                        </div>

                        {{-- Kit (solo CRIMP) --}}
                        @if ($qualIsCrimp && count($qualKits) > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kit (opcional)</label>
                                <select wire:model="qualKitId"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-2 focus:ring-teal-500">
                                    <option value="">Sin kit</option>
                                    @foreach ($qualKits as $kit)
                                        <option value="{{ $kit->id }}">{{ $kit->kit_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Piezas --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Aprobadas *</label>
                                <input wire:model="qualGoodPieces" type="number" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-2 focus:ring-green-500"
                                    placeholder="0">
                                @error('qualGoodPieces')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Rechazadas *</label>
                                <input wire:model="qualBadPieces" type="number" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-2 focus:ring-red-500"
                                    placeholder="0">
                                @error('qualBadPieces')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Discard warning --}}
                        @if ($qualBadPieces > 0)
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 rounded-lg">
                                <div class="flex items-center text-sm text-red-700 dark:text-red-300">
                                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ number_format($qualBadPieces) }} piezas rechazadas seran descartadas.
                                </div>
                            </div>
                        @endif

                        {{-- Fecha --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y Hora *</label>
                            <input wire:model="qualWeighedAt" type="datetime-local"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-2 focus:ring-teal-500">
                            @error('qualWeighedAt')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Comentarios --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios</label>
                            <textarea wire:model="qualComments" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-2 focus:ring-teal-500"
                                placeholder="Observaciones (opcional)..."></textarea>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex gap-3 justify-end">
                        <button wire:click="closeWeighingModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="saveQualityWeighing"
                            class="px-4 py-2 {{ $editingQualityWeighingId ? 'bg-blue-600 hover:bg-blue-700' : 'bg-teal-600 hover:bg-teal-700' }} text-white font-medium rounded-lg transition-colors">
                            {{ $editingQualityWeighingId ? 'Actualizar Pesada' : 'Registrar Pesada' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
