<div class="space-y-6">

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg text-green-800 dark:text-green-300">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm font-medium">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg text-red-800 dark:text-red-300">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Completion Banner --}}
    @if ($allLotsHavePackaging)
        <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/30 border-2 border-green-300 dark:border-green-600 rounded-lg">
            <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-semibold text-green-800 dark:text-green-300">Todos los lotes han sido empacados.</p>
                <p class="text-sm text-green-700 dark:text-green-400 mt-0.5">Puedes cerrar la lista usando el botón "Cerrar y Confirmar Lista" al pie de la página.</p>
            </div>
        </div>
    @endif

    {{-- Packaging Sections per WO --}}
    @forelse ($workOrders as $wo)
        @php $isCrimp = $wo->purchaseOrder->part->is_crimp ?? false; @endphp

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            {{-- WO Header --}}
            <div class="flex items-center gap-4 px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <span class="font-mono font-semibold text-blue-600 dark:text-blue-400">{{ $wo->purchaseOrder->wo ?? $wo->wo_number }}</span>
                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $wo->purchaseOrder->part->number ?? '-' }}</span>
                <span class="text-gray-500 dark:text-gray-400 text-sm truncate flex-1">{{ $wo->purchaseOrder->part->description ?? '' }}</span>
                @if ($isCrimp)
                    <span class="px-2 py-0.5 text-xs font-medium bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 rounded">CRIMP</span>
                @endif
            </div>

            {{-- Lots --}}
            @if ($wo->lots->isNotEmpty())
                <div class="p-4 space-y-4">
                    @foreach ($wo->lots as $lot)
                        @php
                            $available     = (int) $lot->qualityWeighings->sum('good_pieces');
                            $packed        = (int) $lot->packagingRecords->sum('packed_pieces');
                            $surplus       = max(0, $available - $packed);
                            $progressPct   = $available > 0 ? min(100, round(($packed / $available) * 100)) : 0;
                            $progressColor = $progressPct >= 100 ? 'bg-green-500' : ($progressPct > 0 ? 'bg-blue-500' : 'bg-gray-300 dark:bg-gray-600');
                            $hasRecords    = $lot->packagingRecords->isNotEmpty();
                        @endphp

                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                            {{-- Lot Header --}}
                            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-700/40">
                                {{-- Izquierda: lot number + badges de piezas --}}
                                <div class="flex items-center gap-3">
                                    <span class="font-mono text-sm font-semibold text-gray-800 dark:text-gray-200">Lote {{ $lot->lot_number }}</span>
                                    @if ($hasRecords)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Empacado
                                        </span>
                                    @endif
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded">
                                            Disponibles: {{ number_format($available) }}
                                        </span>
                                        <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded">
                                            Empacadas: {{ number_format($packed) }}
                                        </span>
                                        @if ($surplus > 0)
                                            <span class="px-2 py-0.5 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded">
                                                Sobrante: {{ number_format($surplus) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                {{-- Derecha: botones de acción --}}
                                <div class="flex items-center gap-2 flex-wrap">
                                    {{-- Viajero status --}}
                                    @if ($lot->viajero_received)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Viajero &#10003; {{ \Carbon\Carbon::parse($lot->viajero_received_at)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <button wire:click="receiveViajero({{ $lot->id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            Recibir Viajero
                                        </button>
                                    @endif
                                    {{-- Toma de decisiones --}}
                                    @if ($lot->closure_decision)
                                        @php
                                            $decLabel = match($lot->closure_decision) {
                                                'complete_lot' => ['Completar Lote', 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'],
                                                'new_lot'      => ['Nuevo Lote', 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'],
                                                'close_as_is'  => ['Cerrado', 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300'],
                                                default        => ['Decisión', 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'],
                                            };
                                        @endphp
                                        <button wire:click="openDecisionModal({{ $lot->id }})"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium {{ $decLabel[1] }} rounded-lg transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $decLabel[0] }}
                                        </button>
                                    @else
                                        <button wire:click="openDecisionModal({{ $lot->id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Toma de Decisión
                                        </button>
                                    @endif
                                    {{-- Recibí Material (surplus received) --}}
                                    @if ($surplus > 0 || $lot->surplus_received)
                                        @if ($lot->surplus_received)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Mat. Recibido
                                            </span>
                                        @else
                                            <button wire:click="markSurplusReceived({{ $lot->id }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                </svg>
                                                Recibí Material
                                            </button>
                                        @endif
                                    @endif
                                    <button wire:click="openPackagingModal({{ $lot->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Registrar Empaque
                                    </button>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            @if ($available > 0)
                                <div class="px-4 py-2 bg-white dark:bg-gray-800">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="{{ $progressColor }} h-full rounded-full transition-all duration-300" style="width: {{ $progressPct }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400 w-32 text-right">
                                            {{ number_format($packed) }} / {{ number_format($available) }} ({{ $progressPct }}%)
                                        </span>
                                    </div>
                                </div>
                            @endif

                            {{-- Packaging Records --}}
                            @if ($hasRecords)
                                <div class="border-t border-gray-100 dark:border-gray-700">
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-50/70 dark:bg-gray-900/30">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase">Fecha/Hora</th>
                                                <th class="px-4 py-2 text-right font-semibold text-gray-500 dark:text-gray-400 uppercase">Disponibles</th>
                                                <th class="px-4 py-2 text-right font-semibold text-gray-500 dark:text-gray-400 uppercase">Empacadas</th>
                                                <th class="px-4 py-2 text-right font-semibold text-gray-500 dark:text-gray-400 uppercase">Sobrante</th>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase">Empacó</th>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase">Comentarios</th>
                                                <th class="px-4 py-2 text-center font-semibold text-gray-500 dark:text-gray-400 uppercase">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                            @foreach ($lot->packagingRecords as $record)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                        {{ \Carbon\Carbon::parse($record->packed_at)->format('d/m/Y H:i') }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right text-gray-600 dark:text-gray-400">
                                                        {{ number_format($record->available_pieces) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right font-semibold text-green-700 dark:text-green-400">
                                                        {{ number_format($record->packed_pieces) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right {{ $record->surplus_pieces > 0 ? 'text-orange-600 dark:text-orange-400 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                                                        {{ number_format($record->adjusted_surplus ?? $record->surplus_pieces) }}
                                                        @if ($record->adjusted_surplus !== null && $record->adjusted_surplus !== $record->surplus_pieces)
                                                            <span class="text-gray-400 dark:text-gray-500 font-normal">(ajust.)</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                                        {{ $record->packedBy->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                                        {{ $record->comments ?: '-' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-center">
                                                        <button wire:click="deletePackaging({{ $record->id }})"
                                                            wire:confirm="¿Eliminar este registro de empaque?"
                                                            class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50 dark:bg-gray-900/30">
                                            <tr>
                                                <td class="px-4 py-2 font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase">Total</td>
                                                <td class="px-4 py-2 text-right text-xs text-gray-600 dark:text-gray-400">{{ number_format($available) }}</td>
                                                <td class="px-4 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400">{{ number_format($packed) }}</td>
                                                <td class="px-4 py-2 text-right text-xs font-bold {{ $surplus > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-500' }}">{{ number_format($surplus) }}</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="px-4 py-5 text-center text-sm text-gray-400 dark:text-gray-500 italic border-t border-gray-100 dark:border-gray-700">
                                    Sin registros de empaque. Usa "Registrar Empaque" para comenzar.
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-8 text-center text-sm text-gray-400 dark:text-gray-500 italic">
                    Este WO no tiene lotes asignados.
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-10 text-gray-400 dark:text-gray-500">No hay Work Orders en esta lista.</div>
    @endforelse

    {{-- Footer Actions --}}
    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex-wrap">
        {{-- Warning lotes sin viajero --}}
        @php
            $allLots            = $workOrders->flatMap->lots;
            $lotsWithoutViajero = $allLots->filter(fn($l) => !$l->viajero_received && $l->packagingRecords->isNotEmpty());
        @endphp
        @if ($lotsWithoutViajero->isNotEmpty())
            <div class="flex items-center gap-2 text-sm text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 px-4 py-2 rounded-lg border border-amber-200 dark:border-amber-700 mr-auto">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                {{ $lotsWithoutViajero->count() }} lote(s) empacados sin viajero confirmado
            </div>
        @endif
        <button wire:click="openCloseModal"
            @if (!$allLotsHavePackaging) disabled @endif
            class="inline-flex items-center gap-2 px-5 py-2.5 font-semibold rounded-lg shadow transition-colors
                {{ $allLotsHavePackaging
                    ? 'bg-green-600 hover:bg-green-700 text-white'
                    : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Cerrar y Confirmar Lista
            @if (!$allLotsHavePackaging)
                <span class="text-xs font-normal">(faltan lotes)</span>
            @endif
        </button>
    </div>

    {{-- Link a pantalla de Toma de Decisión --}}
    <div class="flex items-center justify-center pt-2">
        <a href="{{ route('admin.sent-lists.display') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            Ver pantalla de Toma de Decision
        </a>
    </div>

    {{-- ===== PACKAGING MODAL ===== --}}
    @if ($showPackagingModal)
        @php
            $modalLot = $workOrders->flatMap->lots->firstWhere('id', $packagingLotId);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closePackagingModal"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-indigo-600 dark:bg-indigo-700">
                    <div>
                        <h3 class="text-lg font-bold text-white">Registrar Empaque</h3>
                        @if ($modalLot)
                            <p class="text-sm text-indigo-100 mt-0.5">
                                Lote {{ $modalLot->lot_number }}
                                &mdash; Disponibles: {{ number_format($modalAvailable) }} pzas
                            </p>
                        @endif
                    </div>
                    <button wire:click="closePackagingModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4">
                    {{-- Available pieces info --}}
                    @if ($modalAvailable === 0)
                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg text-sm text-yellow-800 dark:text-yellow-300">
                            No hay piezas disponibles aprobadas por calidad para este lote.
                        </div>
                    @endif

                    {{-- Packed pieces --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Piezas empacadas <span class="text-red-500">*</span>
                            @if ($modalAvailable > 0)
                                <span class="text-gray-400 dark:text-gray-500 font-normal">(máx. {{ number_format($modalAvailable) }})</span>
                            @endif
                        </label>
                        <input type="number" wire:model="packedPieces" min="0" max="{{ $modalAvailable }}" placeholder="0"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('packedPieces')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Surplus pieces --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas sobrantes <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="surplusPieces" min="0" placeholder="0"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('surplusPieces')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Date/Time --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y hora <span class="text-red-500">*</span></label>
                        <input type="datetime-local" wire:model="packedAt"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('packedAt')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Comments --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios (opcional)</label>
                        <textarea wire:model="packagingComments" rows="2" placeholder="Observaciones de empaque..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closePackagingModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="savePackaging"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
                        Guardar Empaque
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== DECISION MODAL — Control de Materiales ===== --}}
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
                                <h3 id="decision-modal-title" class="text-lg font-semibold text-white">Decisión – Control de Materiales</h3>
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
                                    'new_lot'      => 'Nuevo Lote Creado',
                                    'close_as_is'  => 'Lote Cerrado (faltantes aceptados)',
                                    default        => $decClosureDecision,
                                };
                                $closureColor = match ($decClosureDecision) {
                                    'complete_lot' => 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-700 text-indigo-800 dark:text-indigo-200',
                                    'new_lot'      => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700 text-green-800 dark:text-green-200',
                                    'close_as_is'  => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-700 text-orange-800 dark:text-orange-200',
                                    default        => 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200',
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
                                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-amber-800 dark:text-amber-200">Pendiente: Empaque debe entregar {{ number_format($decSurplus) }} pz sobrantes</span>
                                        </div>
                                    </div>
                                @elseif (!$decSurplusReceived)
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

    {{-- ===== CREATE LOT MODAL (from Decision) ===== --}}
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
                        @if ($decIsCrimp)
                            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-3">
                                <p class="text-xs text-purple-700 dark:text-purple-300">
                                    <strong>Parte con CRIMP:</strong> Se creará un lote y un kit automáticamente. El kit pasará por el flujo Kit → Producción → Calidad.
                                </p>
                            </div>
                        @endif

                        @if ($createLotType === 'new_lot')
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-3">
                                <p class="text-xs text-green-700 dark:text-green-300">
                                    <strong>Nuevo Lote:</strong> El lote actual se cerrará y la lista regresará a <strong>Materiales</strong> para procesar el nuevo lote.
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
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3">
                        <button wire:click="closeCreateLotFormModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button wire:click="confirmCreateLot"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors cursor-pointer">
                            Confirmar y Crear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== CLOSE LIST MODAL ===== --}}
    @if ($showCloseModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-green-600 dark:bg-green-700">
                    <h3 class="text-lg font-bold text-white">Cerrar y Confirmar Lista</h3>
                    <button wire:click="$set('showCloseModal', false)" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-green-800 dark:text-green-300">Confirmar cierre de lista</p>
                            <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                                Esta acción marcará la lista como <strong>Confirmada</strong> y finalizará el flujo de departamentos. Esta acción no se puede deshacer.
                            </p>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lote</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Disponibles</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Empacadas</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Sobrante</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($workOrders as $wo)
                                    @foreach ($wo->lots as $lot)
                                        @php
                                            $availPcs = (int) $lot->qualityWeighings->sum('good_pieces');
                                            $packedPcs = (int) $lot->packagingRecords->sum('packed_pieces');
                                            $surplusPcs = max(0, $availPcs - $packedPcs);
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2.5 font-mono text-gray-800 dark:text-gray-200">{{ $lot->lot_number }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600 dark:text-gray-400">{{ number_format($availPcs) }}</td>
                                            <td class="px-4 py-2.5 text-right font-semibold text-green-700 dark:text-green-400">{{ number_format($packedPcs) }}</td>
                                            <td class="px-4 py-2.5 text-right {{ $surplusPcs > 0 ? 'text-orange-600 dark:text-orange-400 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                                                {{ number_format($surplusPcs) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="$set('showCloseModal', false)"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="closeList"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors font-semibold">
                        Confirmar y Cerrar Lista
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
