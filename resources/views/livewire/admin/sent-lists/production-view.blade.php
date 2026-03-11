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

    {{-- Production Sections per WO --}}
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
                            $totalWeighed  = $lot->weighings->sum('quantity');
                            $targetQty     = $lot->quantity;
                            $remaining     = max(0, $targetQty - $totalWeighed);
                            $progressPct   = $targetQty > 0 ? min(100, round(($totalWeighed / $targetQty) * 100)) : 0;
                            $isCompleted   = $lot->status === 'completed';
                            $progressColor = $isCompleted ? 'bg-green-500' : ($progressPct > 0 ? 'bg-blue-500' : 'bg-gray-300 dark:bg-gray-600');
                        @endphp

                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                            {{-- Lot Header --}}
                            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700/40">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono text-sm font-semibold text-gray-800 dark:text-gray-200">Lote {{ $lot->lot_number }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        Meta: {{ number_format($targetQty) }} pzas
                                        @if ($remaining > 0 && !$isCompleted)
                                            | Restantes: {{ number_format($remaining) }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium {{ $isCompleted ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}">
                                        {{ number_format($totalWeighed) }} / {{ number_format($targetQty) }}
                                    </span>
                                    @if ($isCompleted)
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Lote Completo
                                        </span>
                                        <button wire:click="reopenLot({{ $lot->id }})"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 rounded-lg transition-colors">
                                            Reabrir
                                        </button>
                                    @elseif ($remaining > 0)
                                        <button wire:click="openWeighingModal({{ $lot->id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Agregar Pesada
                                        </button>
                                        {{-- Completar lote parcial --}}
                                        @if ($totalWeighed > 0)
                                            <button wire:click="markLotComplete({{ $lot->id }})"
                                                wire:confirm="¿Marcar el lote como completado con {{ number_format($totalWeighed) }} de {{ number_format($targetQty) }} piezas?"
                                                class="inline-flex items-center gap-1 px-2 py-1.5 text-xs font-medium bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Completar
                                            </button>
                                        @endif
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Restantes: {{ number_format($remaining) }}</span>
                                    @else
                                        <button wire:click="markLotComplete({{ $lot->id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Marcar Completo
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="px-4 py-2 bg-white dark:bg-gray-800">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="{{ $progressColor }} h-full rounded-full transition-all duration-300" style="width: {{ $progressPct }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium {{ $progressPct >= 100 ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }} w-10 text-right">
                                        {{ $progressPct }}%
                                    </span>
                                </div>
                            </div>

                            {{-- Weighings History --}}
                            @php
                                $lotOnlyWeighings = $isCrimp
                                    ? $lot->weighings->whereNull('kit_id')->values()
                                    : $lot->weighings;
                                $lotOnlyTotal = $lotOnlyWeighings->sum('quantity');
                            @endphp
                            @if ($lotOnlyWeighings->isNotEmpty())
                                <div class="border-t border-gray-100 dark:border-gray-700">
                                    @if ($isCrimp)
                                        <div class="px-4 py-1.5 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800">
                                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Pesadas de Lote</span>
                                        </div>
                                    @endif
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-50/70 dark:bg-gray-900/30">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha/Hora</th>
                                                <th class="px-4 py-2 text-right font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Piezas</th>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuario</th>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Comentarios</th>
                                                <th class="px-4 py-2 text-center font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                            @foreach ($lotOnlyWeighings as $weighing)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                        {{ \Carbon\Carbon::parse($weighing->weighed_at)->format('d/m/Y H:i') }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ number_format($weighing->quantity) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                                        {{ $weighing->weighedBy->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                                        {{ $weighing->comments ?: '-' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-center">
                                                        <button wire:click="deleteWeighing({{ $weighing->id }})"
                                                            wire:confirm="¿Eliminar esta pesada?"
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
                                                <td class="px-4 py-2 text-right font-bold text-gray-900 dark:text-white text-xs">{{ number_format($lotOnlyTotal) }}</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="px-4 py-5 text-center text-sm text-gray-400 dark:text-gray-500 italic border-t border-gray-100 dark:border-gray-700">
                                    @if ($isCrimp)
                                        Sin pesadas de lote. Usa "Agregar Pesada" para registrar piezas sin kit.
                                    @else
                                        Sin pesadas registradas. Usa "Agregar Pesada" para comenzar.
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Pesadas por Kit para CRIMP --}}
                @if ($isCrimp && $wo->kits->isNotEmpty())
                    <div class="mt-2 border border-purple-200 dark:border-purple-700 rounded-lg overflow-hidden">
                        <div class="px-4 py-2.5 bg-purple-50 dark:bg-purple-900/20 border-b border-purple-200 dark:border-purple-700">
                            <h4 class="text-sm font-semibold text-purple-800 dark:text-purple-300">Pesadas por Kit (CRIMP)</h4>
                        </div>
                        <div class="divide-y divide-purple-100 dark:divide-purple-800">
                            @foreach ($wo->kits as $kit)
                                @php
                                    $kitWeighings = $wo->lots->flatMap->weighings->where('kit_id', $kit->id)->values();
                                    $kitWeighed   = $kitWeighings->sum('quantity');
                                    $kitTarget    = $kit->quantity;
                                    $kitPct       = $kitTarget > 0 ? min(100, round(($kitWeighed / $kitTarget) * 100)) : 0;
                                    $kitBarColor  = $kitPct >= 100 ? 'bg-green-500' : ($kitPct > 0 ? 'bg-purple-500' : 'bg-gray-300 dark:bg-gray-600');
                                    // First lot associated with this kit for the modal
                                    $kitFirstLot  = $wo->lots->first();
                                @endphp
                                <div class="px-4 py-3">
                                    {{-- Kit Header --}}
                                    <div class="flex items-center justify-between gap-3 mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-sm font-semibold text-purple-700 dark:text-purple-300">{{ $kit->kit_number }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Meta: {{ number_format($kitTarget) }} pzas</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium {{ $kitPct >= 100 ? 'text-green-600 dark:text-green-400' : 'text-purple-600 dark:text-purple-400' }}">
                                                {{ number_format($kitWeighed) }} / {{ number_format($kitTarget) }} ({{ $kitPct }}%)
                                            </span>
                                            @if ($kitFirstLot)
                                                <button wire:click="openKitWeighingModal({{ $kitFirstLot->id }}, {{ $kit->id }})"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    Agregar Pesada
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- Progress bar --}}
                                    <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-2">
                                        <div class="{{ $kitBarColor }} h-full rounded-full transition-all duration-300" style="width: {{ $kitPct }}%"></div>
                                    </div>
                                    {{-- Kit weighings table --}}
                                    @if ($kitWeighings->isNotEmpty())
                                        <table class="w-full text-xs mt-1">
                                            <thead class="bg-gray-50/70 dark:bg-gray-900/30">
                                                <tr>
                                                    <th class="px-3 py-1.5 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase">Fecha/Hora</th>
                                                    <th class="px-3 py-1.5 text-right font-semibold text-gray-500 dark:text-gray-400 uppercase">Piezas</th>
                                                    <th class="px-3 py-1.5 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase">Usuario</th>
                                                    <th class="px-3 py-1.5 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase">Comentarios</th>
                                                    <th class="px-3 py-1.5 text-center font-semibold text-gray-500 dark:text-gray-400 uppercase">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                @foreach ($kitWeighings as $kw)
                                                    <tr class="hover:bg-purple-50 dark:hover:bg-purple-900/10 transition-colors">
                                                        <td class="px-3 py-1.5 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                            {{ \Carbon\Carbon::parse($kw->weighed_at)->format('d/m/Y H:i') }}
                                                        </td>
                                                        <td class="px-3 py-1.5 text-right font-semibold text-purple-700 dark:text-purple-300">
                                                            {{ number_format($kw->quantity) }}
                                                        </td>
                                                        <td class="px-3 py-1.5 text-gray-600 dark:text-gray-400">
                                                            {{ $kw->weighedBy->name ?? 'N/A' }}
                                                        </td>
                                                        <td class="px-3 py-1.5 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                                            {{ $kw->comments ?: '-' }}
                                                        </td>
                                                        <td class="px-3 py-1.5 text-center">
                                                            <button wire:click="deleteWeighing({{ $kw->id }})"
                                                                wire:confirm="¿Eliminar esta pesada de kit?"
                                                                class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-colors">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-xs text-gray-400 dark:text-gray-500 italic">Sin pesadas para este kit aún.</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

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
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Enviar a Calidad
        </button>
    </div>

    {{-- ===== ADD WEIGHING MODAL ===== --}}
    @if ($showWeighingModal)
        @php
            $modalLot       = $workOrders->flatMap->lots->firstWhere('id', $weighingLotId);
            $modalWo        = $modalLot ? $workOrders->firstWhere('id', $modalLot->work_order_id) : null;
            $modalIsCrimp   = $modalWo ? ($modalWo->purchaseOrder->part->is_crimp ?? false) : false;
            $modalRemaining = $modalLot ? max(0, $modalLot->quantity - $modalLot->weighings->sum('quantity')) : 0;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closeWeighingModal"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-blue-600 dark:bg-blue-700">
                    <div>
                        <h3 class="text-lg font-bold text-white">Registrar Pesada</h3>
                        @if ($modalLot)
                            <p class="text-sm text-blue-100 mt-0.5">
                                Lote {{ $modalLot->lot_number }}
                                &mdash; Restantes: {{ number_format($modalRemaining) }} pzas
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
                    {{-- Quantity --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Piezas pesadas <span class="text-red-500">*</span>
                            @if ($modalRemaining > 0)
                                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(máx. {{ number_format($modalRemaining) }})</span>
                            @endif
                        </label>
                        <input type="number" wire:model="weighingQuantity" min="1" max="{{ $modalRemaining }}" placeholder="0"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('weighingQuantity')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kit selector (CRIMP only) --}}
                    @if ($modalIsCrimp && $modalWo && $modalWo->kits->isNotEmpty())
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kit asociado</label>
                            <select wire:model="weighingKitId"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Sin kit --</option>
                                @foreach ($modalWo->kits as $kit)
                                    <option value="{{ $kit->id }}">{{ $kit->kit_number }} ({{ number_format($kit->quantity) }} pzas)</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Date/Time --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y hora <span class="text-red-500">*</span></label>
                        <input type="datetime-local" wire:model="weighingAt"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('weighingAt')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Comments --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios (opcional)</label>
                        <textarea wire:model="weighingComments" rows="2" placeholder="Observaciones..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeWeighingModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="saveWeighing"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        Guardar Pesada
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== SEND TO QUALITY MODAL ===== --}}
    @if ($showSendModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closeSendModal"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-blue-600 dark:bg-blue-700">
                    <h3 class="text-lg font-bold text-white">Enviar a Calidad</h3>
                    <button wire:click="closeSendModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Resumen de piezas producidas por lote:</p>

                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lote</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Meta</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Pesadas</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($workOrders as $wo)
                                    @foreach ($wo->lots as $lot)
                                        @php $weighed = $lot->weighings->sum('quantity'); @endphp
                                        <tr>
                                            <td class="px-4 py-2.5 font-mono text-gray-800 dark:text-gray-200">{{ $lot->lot_number }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600 dark:text-gray-400">{{ number_format($lot->quantity) }}</td>
                                            <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-white">{{ number_format($weighed) }}</td>
                                            <td class="px-4 py-2.5 text-right">
                                                @php $pct = $lot->quantity > 0 ? min(100, round($weighed / $lot->quantity * 100)) : 0; @endphp
                                                <span class="{{ $pct >= 100 ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }} font-medium">{{ $pct }}%</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas (opcional)</label>
                        <textarea wire:model="sendNotes" rows="2" placeholder="Observaciones para Calidad..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeSendModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="sendToQuality"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        Confirmar Envío
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
