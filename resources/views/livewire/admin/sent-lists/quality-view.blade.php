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

    {{-- Quality Sections per WO --}}
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
                            $prodTotal              = $lot->weighings->sum('quantity');
                            $qualityGood            = $lot->qualityWeighings->sum('good_pieces');
                            $qualityBad             = $lot->qualityWeighings->sum('bad_pieces');
                            $qualityTotal           = $qualityGood + $qualityBad;
                            $pendingPieces          = max(0, $prodTotal - $qualityTotal);
                            $progressPct            = $prodTotal > 0 ? min(100, round(($qualityTotal / $prodTotal) * 100)) : 0;
                            $progressColor          = $progressPct >= 100 ? 'bg-green-500' : ($progressPct > 0 ? 'bg-yellow-500' : 'bg-gray-300 dark:bg-gray-600');
                            $lotIsReadyForQuality   = $lot->status === 'completed' || $lot->weighings->isNotEmpty();
                            $lotProductionInProgress = $lot->weighings->isNotEmpty() && $lot->status !== 'completed';
                        @endphp

                        @if (!$lotIsReadyForQuality)
                            {{-- Lot not yet in production --}}
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-700/40">
                                    <span class="font-mono text-sm font-semibold text-gray-500 dark:text-gray-400">Lote {{ $lot->lot_number }}</span>
                                    <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded">
                                        Pendiente de producción
                                    </span>
                                </div>
                                <div class="px-4 py-4 text-sm text-gray-400 dark:text-gray-500 italic text-center border-t border-gray-100 dark:border-gray-700">
                                    Este lote aún no tiene pesadas de producción registradas.
                                </div>
                            </div>
                        @else

                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                            {{-- Warning: production still in progress --}}
                            @if ($lotProductionInProgress)
                                <div class="flex items-center gap-2 px-4 py-2 bg-yellow-50 dark:bg-yellow-900/20 border-b border-yellow-200 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 text-xs">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                    Producción aún en progreso — datos parciales ({{ number_format($prodTotal) }} pzas pesadas)
                                </div>
                            @endif
                            {{-- Lot Header --}}
                            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-700/40">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono text-sm font-semibold text-gray-800 dark:text-gray-200">Lote {{ $lot->lot_number }}</span>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded">
                                            Recibidas: {{ number_format($prodTotal) }}
                                        </span>
                                        @if ($qualityGood > 0)
                                            <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded">
                                                Buenas: {{ number_format($qualityGood) }}
                                            </span>
                                        @endif
                                        @if ($qualityBad > 0)
                                            <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded">
                                                Malas: {{ number_format($qualityBad) }}
                                            </span>
                                        @endif
                                        @if ($pendingPieces > 0)
                                            <span class="px-2 py-0.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded">
                                                Pendientes: {{ number_format($pendingPieces) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <button wire:click="openWeighingModal({{ $lot->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Registrar Pesada
                                </button>
                            </div>

                            {{-- Progress Bar --}}
                            @if ($prodTotal > 0)
                                <div class="px-4 py-2 bg-white dark:bg-gray-800">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="{{ $progressColor }} h-full rounded-full transition-all duration-300" style="width: {{ $progressPct }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400 w-32 text-right">
                                            {{ number_format($qualityTotal) }} / {{ number_format($prodTotal) }} ({{ $progressPct }}%)
                                        </span>
                                    </div>
                                </div>
                            @endif

                            {{-- Quality Weighings History --}}
                            @if ($lot->qualityWeighings->isNotEmpty())
                                <div class="border-t border-gray-100 dark:border-gray-700">
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-50/70 dark:bg-gray-900/30">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase">Fecha/Hora</th>
                                                <th class="px-4 py-2 text-right font-semibold text-gray-500 dark:text-gray-400 uppercase">Recibidas</th>
                                                <th class="px-4 py-2 text-right font-semibold text-gray-500 dark:text-gray-400 uppercase">Buenas</th>
                                                <th class="px-4 py-2 text-right font-semibold text-gray-500 dark:text-gray-400 uppercase">Malas</th>
                                                @if ($isCrimp)
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase">Kit</th>
                                                @endif
                                                <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase">Usuario</th>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase">Comentarios</th>
                                                <th class="px-4 py-2 text-center font-semibold text-gray-500 dark:text-gray-400 uppercase">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                            @foreach ($lot->qualityWeighings as $qw)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                        {{ \Carbon\Carbon::parse($qw->weighed_at)->format('d/m/Y H:i') }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right text-gray-600 dark:text-gray-400">{{ number_format($qw->production_good_pieces) }}</td>
                                                    <td class="px-4 py-2 text-right font-semibold text-green-700 dark:text-green-400">{{ number_format($qw->good_pieces) }}</td>
                                                    <td class="px-4 py-2 text-right font-semibold {{ $qw->bad_pieces > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                                                        {{ number_format($qw->bad_pieces) }}
                                                    </td>
                                                    @if ($isCrimp)
                                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                                            {{ $qw->kit ? $qw->kit->kit_number : '-' }}
                                                        </td>
                                                    @endif
                                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                                        {{ $qw->weighedBy->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                                        {{ $qw->comments ?: '-' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-center">
                                                        <button wire:click="deleteWeighing({{ $qw->id }})"
                                                            wire:confirm="¿Eliminar esta pesada de calidad?"
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
                                                <td class="px-4 py-2 text-right text-xs text-gray-600 dark:text-gray-400">{{ number_format($prodTotal) }}</td>
                                                <td class="px-4 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400">{{ number_format($qualityGood) }}</td>
                                                <td class="px-4 py-2 text-right text-xs font-bold {{ $qualityBad > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500' }}">{{ number_format($qualityBad) }}</td>
                                                @if ($isCrimp) <td></td> @endif
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="px-4 py-5 text-center text-sm text-gray-400 dark:text-gray-500 italic border-t border-gray-100 dark:border-gray-700">
                                    Sin pesadas de calidad. Usa "Registrar Pesada" para comenzar.
                                </div>
                            @endif
                        </div>
                        @endif {{-- end $lotIsReadyForQuality --}}
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
    <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
        <button wire:click="openSendModal"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg shadow transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            Enviar a Empaque
        </button>
    </div>

    {{-- ===== QUALITY WEIGHING MODAL ===== --}}
    @if ($showWeighingModal)
        @php
            $modalLot      = $workOrders->flatMap->lots->firstWhere('id', $weighingLotId);
            $modalWo       = $modalLot ? $workOrders->firstWhere('id', $modalLot->work_order_id) : null;
            $modalIsCrimp  = $modalWo ? ($modalWo->purchaseOrder->part->is_crimp ?? false) : false;
            $modalProdTotal = $modalLot ? $modalLot->weighings->sum('quantity') : 0;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closeWeighingModal"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-yellow-600 dark:bg-yellow-700">
                    <div>
                        <h3 class="text-lg font-bold text-white">Pesada de Calidad</h3>
                        @if ($modalLot)
                            <p class="text-sm text-yellow-100 mt-0.5">
                                Lote {{ $modalLot->lot_number }}
                                &mdash; Producción: {{ number_format($modalProdTotal) }} pzas
                            </p>
                        @endif
                    </div>
                    <button wire:click="closeWeighingModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4">
                    {{-- Good pieces --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas buenas <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="goodPieces" min="0" placeholder="0"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        @error('goodPieces')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Bad pieces --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas rechazadas</label>
                        <input type="number" wire:model="badPieces" min="0" placeholder="0"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        @error('badPieces')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Comments shown prominently if bad pieces > 0 --}}
                    @if ($badPieces > 0)
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg text-sm text-red-700 dark:text-red-300">
                            Hay {{ $badPieces }} pieza(s) rechazada(s). Documente el motivo en los comentarios.
                        </div>
                    @endif

                    {{-- Kit selector (CRIMP only) --}}
                    @if ($modalIsCrimp && $modalWo && $modalWo->kits->isNotEmpty())
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kit asociado</label>
                            <select wire:model="weighingKitId"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <option value="">-- Sin kit --</option>
                                @foreach ($modalWo->kits as $kit)
                                    <option value="{{ $kit->id }}">{{ $kit->kit_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Date/Time --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y hora <span class="text-red-500">*</span></label>
                        <input type="datetime-local" wire:model="weighingAt"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        @error('weighingAt')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Comments --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios{{ $badPieces > 0 ? ' (recomendado)' : ' (opcional)' }}</label>
                        <textarea wire:model="weighingComments" rows="2" placeholder="Observaciones de calidad..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 resize-none"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeWeighingModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="saveWeighing"
                        class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg transition-colors">
                        Guardar Pesada
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== SEND TO PACKAGING MODAL ===== --}}
    @if ($showSendModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closeSendModal"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-yellow-600 dark:bg-yellow-700">
                    <h3 class="text-lg font-bold text-white">Enviar a Empaque</h3>
                    <button wire:click="closeSendModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Resumen de piezas aprobadas por calidad:</p>

                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lote</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Recibidas</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Buenas</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Rechazadas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($workOrders as $wo)
                                    @foreach ($wo->lots as $lot)
                                        <tr>
                                            <td class="px-4 py-2.5 font-mono text-gray-800 dark:text-gray-200">{{ $lot->lot_number }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600 dark:text-gray-400">{{ number_format($lot->weighings->sum('quantity')) }}</td>
                                            <td class="px-4 py-2.5 text-right font-semibold text-green-700 dark:text-green-400">{{ number_format($lot->qualityWeighings->sum('good_pieces')) }}</td>
                                            <td class="px-4 py-2.5 text-right {{ $lot->qualityWeighings->sum('bad_pieces') > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                                                {{ number_format($lot->qualityWeighings->sum('bad_pieces')) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas (opcional)</label>
                        <textarea wire:model="sendNotes" rows="2" placeholder="Observaciones para Empaque..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 resize-none"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeSendModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="sendToPackaging"
                        class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg transition-colors">
                        Confirmar Envío
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
