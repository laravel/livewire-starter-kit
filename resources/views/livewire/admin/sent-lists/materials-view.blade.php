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

    {{-- Unresolved Rejections Panel --}}
    @if ($sentList->unresolvedRejections->isNotEmpty())
        <div class="p-4 bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-700 rounded-lg">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <h4 class="font-semibold text-red-700 dark:text-red-300">Rechazos Pendientes de Corrección ({{ $sentList->unresolvedRejections->count() }})</h4>
            </div>
            <div class="space-y-2">
                @foreach ($sentList->unresolvedRejections as $rejection)
                    <div class="p-3 bg-white dark:bg-gray-800 border border-red-200 dark:border-red-600 rounded-lg text-sm">
                        <div class="flex flex-wrap gap-4">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Rechazado por:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200 ml-1">{{ $rejection->rejectedBy->name ?? 'N/A' }}</span>
                            </div>
                            @if ($rejection->lot)
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Lote:</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200 ml-1">{{ $rejection->lot->lot_number }}</span>
                                </div>
                            @endif
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Fecha:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200 ml-1">{{ $rejection->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="text-gray-500 dark:text-gray-400">Motivo:</span>
                            <span class="text-red-700 dark:text-red-300 ml-1">{{ $rejection->reason }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Work Orders Table --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Work Orders en la Lista</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">WO #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Parte</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cant. WO</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lotes</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kits</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($workOrders as $wo)
                        @php
                            $isCrimp        = $wo->purchaseOrder->part->is_crimp ?? false;
                            $hasLots        = $wo->lots->isNotEmpty();
                            $hasKits        = $wo->kits->isNotEmpty();
                            $isReady        = $hasLots && (!$isCrimp || $hasKits);
                            $semaphore      = $isReady ? 'green' : ($hasLots && $isCrimp && !$hasKits ? 'yellow' : 'red');
                            $rejectedLotIds = $sentList->unresolvedRejections->pluck('lot_id')->filter()->toArray();
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $wo->lots->pluck('id')->intersect($rejectedLotIds)->isNotEmpty() ? 'bg-red-50 dark:bg-red-900/10 border-l-4 border-red-400' : '' }}">
                            <td class="px-4 py-3 font-mono font-medium text-blue-600 dark:text-blue-400">
                                {{ $wo->purchaseOrder->wo ?? $wo->wo_number }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $wo->purchaseOrder->part->number ?? '-' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $wo->purchaseOrder->part->description ?? '' }}</div>
                                @if ($isCrimp)
                                    <span class="inline-block mt-1 px-1.5 py-0.5 text-xs bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 rounded font-medium">CRIMP</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                {{ number_format($wo->original_quantity) }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($hasLots)
                                    <div class="space-y-1">
                                        @foreach ($wo->lots as $lot)
                                            @php $isRejectedLot = in_array($lot->id, $rejectedLotIds); @endphp
                                            <div class="flex items-center gap-2 text-xs">
                                                @if (!$isCrimp)
                                                    @php
                                                        $matSt    = $lot->material_status ?? 'pending';
                                                        $matColor = match($matSt) { 'released' => 'bg-green-500', 'rejected' => 'bg-red-500', default => 'bg-gray-400' };
                                                        $matTitle = match($matSt) { 'released' => 'Aprobado', 'rejected' => 'Rechazado', default => 'Pendiente' };
                                                    @endphp
                                                    <button wire:click="openMaterialModal({{ $lot->id }})"
                                                        class="w-3 h-3 rounded-full {{ $matColor }} hover:opacity-75 flex-shrink-0 cursor-pointer"
                                                        title="Material: {{ $matTitle }}"></button>
                                                @endif
                                                <span class="px-2 py-0.5 rounded font-mono
                                                    {{ $isRejectedLot
                                                        ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-600 font-bold'
                                                        : 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300' }}">
                                                    @if($isRejectedLot) &#9888; @endif{{ $lot->lot_number }}
                                                </span>
                                                <span class="{{ $isRejectedLot ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                                                    {{ number_format($lot->quantity) }} pzas
                                                </span>
                                                @if($isRejectedLot)
                                                    @php $lotRejection = $sentList->unresolvedRejections->firstWhere('lot_id', $lot->id); @endphp
                                                    @if($lotRejection)
                                                        <span class="text-red-600 dark:text-red-400 italic truncate max-w-xs" title="{{ $lotRejection->reason }}">
                                                            &mdash; {{ Str::limit($lotRejection->reason, 40) }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            Total: {{ number_format($wo->lots->sum('quantity')) }} / {{ number_format($wo->original_quantity) }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500 italic">Sin lotes</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($isCrimp)
                                    @if ($hasKits)
                                        <div class="space-y-1">
                                            @foreach ($wo->kits as $kit)
                                                @php
                                                    $kitSt    = $kit->status ?? 'preparing';
                                                    $kitColor = match($kitSt) {
                                                        'released'    => 'bg-green-500',
                                                        'ready'       => 'bg-indigo-500',
                                                        'in_assembly' => 'bg-orange-500',
                                                        'rejected'    => 'bg-red-500',
                                                        default       => 'bg-yellow-400',
                                                    };
                                                    $kitTitle = match($kitSt) {
                                                        'released'    => 'Liberado',
                                                        'ready'       => 'Listo',
                                                        'in_assembly' => 'En ensamble',
                                                        'rejected'    => 'Rechazado',
                                                        default       => 'Preparando',
                                                    };
                                                @endphp
                                                <div class="flex items-center gap-2 text-xs">
                                                    <button wire:click="openKitStatusModal({{ $kit->id }})"
                                                        class="w-3 h-3 rounded-full {{ $kitColor }} hover:opacity-75 flex-shrink-0 cursor-pointer"
                                                        title="Kit: {{ $kitTitle }}"></button>
                                                    <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded font-mono">{{ $kit->kit_number }}</span>
                                                    <span class="text-gray-500 dark:text-gray-400">{{ number_format($kit->quantity) }} pzas</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500 italic">Sin kits</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($semaphore === 'green')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Listo
                                    </span>
                                @elseif ($semaphore === 'yellow')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300">
                                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span> Sin kits
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span> Sin lotes
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    <button wire:click="openLotModal({{ $wo->id }})"
                                        class="px-3 py-1.5 text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                        Gestionar Lotes
                                    </button>
                                    @if ($isCrimp)
                                        <button wire:click="openKitModal({{ $wo->id }})"
                                            class="px-3 py-1.5 text-xs font-medium bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                                            Crear Kit
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-400 dark:text-gray-500">
                                No hay Work Orders en esta lista.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
        <button wire:click="openSendModal"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Enviar a Inspección
        </button>
    </div>

    {{-- ===== LOT MODAL ===== --}}
    @if ($showLotModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data>
            <div class="absolute inset-0 bg-black/60" wire:click="closeLotModal"></div>
            <div class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 bg-blue-600 dark:bg-blue-700">
                    <div>
                        <h3 class="text-lg font-bold text-white">Gestionar Lotes</h3>
                        @php $selectedWo = $workOrders->firstWhere('id', $selectedWorkOrderId); @endphp
                        @if ($selectedWo)
                            <p class="text-sm text-blue-100 mt-0.5">
                                {{ $selectedWo->purchaseOrder->wo ?? $selectedWo->wo_number }} &mdash; {{ $selectedWo->purchaseOrder->part->number ?? '' }}
                                (Cant. WO: {{ number_format($selectedWo->original_quantity) }})
                            </p>
                        @endif
                    </div>
                    <button wire:click="closeLotModal" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 max-h-[55vh] overflow-y-auto space-y-3">
                    @error('lots')
                        <div class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-300 text-sm">
                            {{ $message }}
                        </div>
                    @enderror

                    @forelse ($lots as $index => $lot)
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex-1 grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">No. Lote / Viajero</label>
                                    <input type="text" wire:model="lots.{{ $index }}.number"
                                        placeholder="Ej: 001"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @error("lots.{$index}.number")
                                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cantidad</label>
                                    <input type="number" wire:model="lots.{{ $index }}.quantity"
                                        placeholder="0" min="1"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @error("lots.{$index}.quantity")
                                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <button wire:click="removeLotRow({{ $index }})"
                                class="mt-5 p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                title="Eliminar fila">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-center text-sm text-gray-400 dark:text-gray-500 py-4">No hay lotes. Agrega uno nuevo.</p>
                    @endforelse

                    <button wire:click="addLotRow"
                        class="w-full py-2.5 border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:border-blue-400 hover:text-blue-500 rounded-lg transition-colors text-sm font-medium flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar Fila de Lote
                    </button>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeLotModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="saveLots"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        Guardar Lotes
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== KIT MODAL ===== --}}
    @if ($showKitModal)
        @php $kitWo = $workOrders->firstWhere('id', $kitWorkOrderId); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closeKitModal"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 bg-purple-600 dark:bg-purple-700">
                    <div>
                        <h3 class="text-lg font-bold text-white">Crear Kit (CRIMP)</h3>
                        @if ($kitWo)
                            <p class="text-sm text-purple-100 mt-0.5">{{ $kitWo->purchaseOrder->wo ?? $kitWo->wo_number }} &mdash; {{ $kitWo->purchaseOrder->part->number ?? '' }}</p>
                        @endif
                    </div>
                    <button wire:click="closeKitModal" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 space-y-4">
                    {{-- Lot checkboxes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Lotes a incluir en el Kit</label>
                        @error('kitLots')
                            <p class="text-xs text-red-600 dark:text-red-400 mb-2">{{ $message }}</p>
                        @enderror
                        @if ($kitWo && $kitWo->lots->isNotEmpty())
                            <div class="space-y-2">
                                @foreach ($kitWo->lots as $lot)
                                    <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <input type="checkbox" wire:model="kitLots" value="{{ $lot->id }}"
                                            class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-purple-600 focus:ring-purple-500">
                                        <div>
                                            <span class="font-mono text-sm font-medium text-gray-900 dark:text-gray-100">{{ $lot->lot_number }}</span>
                                            <span class="text-gray-500 dark:text-gray-400 ml-2 text-sm">{{ number_format($lot->quantity) }} pzas</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500 italic">Este WO no tiene lotes. Crea lotes primero.</p>
                        @endif
                    </div>

                    {{-- Quantity --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad del Kit</label>
                        <input type="number" wire:model="kitQuantity" min="1" placeholder="0"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @error('kitQuantity')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas (opcional)</label>
                        <textarea wire:model="kitNotes" rows="2" placeholder="Observaciones del kit..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeKitModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="saveKit"
                        class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
                        Crear Kit
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== SEND TO INSPECTION MODAL ===== --}}
    @if ($showSendModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closeSendModal"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-green-600 dark:bg-green-700">
                    <h3 class="text-lg font-bold text-white">Confirmar Envío a Inspección</h3>
                    <button wire:click="closeSendModal" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        La lista pasará de <strong class="text-gray-900 dark:text-white">Materiales</strong> a <strong class="text-gray-900 dark:text-white">Inspección</strong>. Esta acción resolverá automáticamente los rechazos pendientes.
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas de envío (opcional)</label>
                        <textarea wire:model="sendNotes" rows="3" placeholder="Observaciones para Inspección..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeSendModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="sendToInspection"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                        Confirmar Envío
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MATERIAL STATUS MODAL (non-CRIMP) ===== --}}
    @if ($showMaterialModal)
        @php $matLot = \App\Models\Lot::find($materialLotId); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closeMaterialModal"></div>
            <div class="relative w-full max-w-sm bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-teal-600 dark:bg-teal-700">
                    <div>
                        <h3 class="text-lg font-bold text-white">Estado de Material</h3>
                        @if ($matLot)
                            <p class="text-sm text-teal-100 mt-0.5">Lote {{ $matLot->lot_number }} &mdash; {{ number_format($matLot->quantity) }} pzas</p>
                        @endif
                    </div>
                    <button wire:click="closeMaterialModal" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado del material</label>
                    <div class="space-y-2">
                        @foreach (['pending' => ['Pendiente', 'bg-gray-400'], 'released' => ['Aprobado / Liberado', 'bg-green-500'], 'rejected' => ['Rechazado', 'bg-red-500']] as $val => [$label, $dot])
                            <label class="flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-colors
                                {{ $materialStatus === $val ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                <input type="radio" wire:model.live="materialStatus" value="{{ $val }}" class="sr-only">
                                <span class="w-4 h-4 rounded-full {{ $dot }} flex-shrink-0"></span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeMaterialModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="saveMaterial"
                        class="px-4 py-2 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition-colors">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== KIT STATUS MODAL (CRIMP) ===== --}}
    @if ($showKitStatusModal)
        @php $statusKit = \App\Models\Kit::find($kitStatusId); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closeKitStatusModal"></div>
            <div class="relative w-full max-w-sm bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-purple-600 dark:bg-purple-700">
                    <div>
                        <h3 class="text-lg font-bold text-white">Estado del Kit</h3>
                        @if ($statusKit)
                            <p class="text-sm text-purple-100 mt-0.5">{{ $statusKit->kit_number }} &mdash; {{ number_format($statusKit->quantity) }} pzas</p>
                        @endif
                    </div>
                    <button wire:click="closeKitStatusModal" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado del kit</label>
                    <div class="space-y-2">
                        @foreach ([
                            'preparing'   => ['Preparando',   'bg-yellow-400'],
                            'ready'       => ['Listo',         'bg-indigo-500'],
                            'released'    => ['Liberado',      'bg-green-500'],
                            'in_assembly' => ['En ensamble',   'bg-orange-500'],
                            'rejected'    => ['Rechazado',     'bg-red-500'],
                        ] as $val => [$label, $dot])
                            <label class="flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-colors
                                {{ $kitStatusValue === $val ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                <input type="radio" wire:model.live="kitStatusValue" value="{{ $val }}" class="sr-only">
                                <span class="w-4 h-4 rounded-full {{ $dot }} flex-shrink-0"></span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeKitStatusModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="saveKitStatus"
                        class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
