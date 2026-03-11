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

    {{-- Summary Banner --}}
    @php
        $allLots = $workOrders->flatMap->lots;
        $totalLots = $allLots->count();
        $approvedCount = $allLots->where('inspection_status', 'approved')->count();
        $rejectedCount = $allLots->where('inspection_status', 'rejected')->count();
        $pendingCount  = $allLots->where('inspection_status', 'pending')->count();
    @endphp
    <div class="grid grid-cols-3 gap-4">
        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center border border-gray-200 dark:border-gray-600">
            <div class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ $pendingCount }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pendientes</div>
        </div>
        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg text-center border border-green-200 dark:border-green-700">
            <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $approvedCount }}</div>
            <div class="text-xs text-green-600 dark:text-green-400 mt-1">Aprobados</div>
        </div>
        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg text-center border border-red-200 dark:border-red-700">
            <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $rejectedCount }}</div>
            <div class="text-xs text-red-600 dark:text-red-400 mt-1">Rechazados</div>
        </div>
    </div>

    {{-- Work Orders with Lots --}}
    <div class="space-y-4">
        @forelse ($workOrders as $wo)
            @php $isCrimp = $wo->purchaseOrder->part->is_crimp ?? false; @endphp
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                {{-- WO Header --}}
                <div class="flex items-center gap-4 px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <span class="font-mono font-semibold text-blue-600 dark:text-blue-400">{{ $wo->purchaseOrder->wo ?? $wo->wo_number }}</span>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $wo->purchaseOrder->part->number ?? '-' }}</span>
                    <span class="text-gray-500 dark:text-gray-400 text-sm truncate flex-1">{{ $wo->purchaseOrder->part->description ?? '' }}</span>
                    @if ($isCrimp)
                        <span class="px-2 py-0.5 text-xs font-medium bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 rounded">CRIMP</span>
                    @endif
                </div>

                {{-- Lots Table --}}
                @if ($wo->lots->isNotEmpty())
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/50 dark:bg-gray-900/30">
                            <tr>
                                <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lote</th>
                                <th class="px-5 py-2.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                                @if ($isCrimp)
                                    <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Kits</th>
                                @endif
                                <th class="px-5 py-2.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Estado Inspección</th>
                                <th class="px-5 py-2.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($wo->lots as $lot)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-5 py-3 font-mono font-medium text-gray-900 dark:text-gray-100">
                                        {{ $lot->lot_number }}
                                        @if ($lot->inspection_comments && $lot->inspection_status === 'rejected')
                                            <div class="text-xs text-red-600 dark:text-red-400 mt-0.5 font-sans font-normal">{{ Str::limit($lot->inspection_comments, 60) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">
                                        {{ number_format($lot->quantity) }}
                                    </td>
                                    @if ($isCrimp)
                                        <td class="px-5 py-3">
                                            @php $lotKits = $wo->kits->filter(fn($k) => $k->lots->contains('id', $lot->id)); @endphp
                                            @if ($lotKits->isNotEmpty())
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($lotKits as $kit)
                                                        <span class="px-1.5 py-0.5 text-xs bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded font-mono">{{ $kit->kit_number }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-gray-500 italic">Sin kit</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-5 py-3 text-center">
                                        @if ($lot->inspection_status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Aprobado
                                            </span>
                                        @elseif ($lot->inspection_status === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Rechazado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <button wire:click="approveLot({{ $lot->id }})"
                                                @if ($lot->inspection_status === 'approved') disabled @endif
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors
                                                    {{ $lot->inspection_status === 'approved'
                                                        ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                                                        : 'bg-green-600 hover:bg-green-700 text-white' }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Aprobar
                                            </button>
                                            <button wire:click="openRejectModal({{ $lot->id }})"
                                                @if ($lot->inspection_status === 'rejected') disabled @endif
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors
                                                    {{ $lot->inspection_status === 'rejected'
                                                        ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                                                        : 'bg-red-600 hover:bg-red-700 text-white' }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                Rechazar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-5 py-6 text-center text-sm text-gray-400 dark:text-gray-500 italic">Este WO no tiene lotes asignados.</div>
                @endif
            </div>
        @empty
            <div class="text-center py-10 text-gray-400 dark:text-gray-500">No hay Work Orders en esta lista.</div>
        @endforelse
    </div>

    {{-- Footer Actions --}}
    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        <div>
            <button wire:click="openReturnModal"
                class="inline-flex items-center gap-2 px-5 py-2.5 {{ $hasRejected ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-500 hover:bg-gray-600' }} text-white font-semibold rounded-lg shadow transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                Regresar a Materiales
            </button>
        </div>
        <button wire:click="openApproveModal"
            @if (!$allApproved) disabled @endif
            class="inline-flex items-center gap-2 px-5 py-2.5 font-semibold rounded-lg shadow transition-colors
                {{ $allApproved
                    ? 'bg-green-600 hover:bg-green-700 text-white'
                    : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Aprobar y Enviar a Producción
            @if (!$allApproved)
                <span class="text-xs font-normal">(faltan lotes)</span>
            @endif
        </button>
    </div>

    {{-- ===== REJECT LOT MODAL ===== --}}
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closeRejectModal"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-red-600 dark:bg-red-700">
                    <h3 class="text-lg font-bold text-white">Rechazar Lote</h3>
                    <button wire:click="closeRejectModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Indique el motivo del rechazo. Esta información quedará registrada en el historial.</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo del rechazo <span class="text-red-500">*</span></label>
                        <textarea wire:model="rejectReason" rows="4" placeholder="Describa el problema encontrado (mínimo 5 caracteres)..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                        @error('rejectReason')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeRejectModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="rejectLot"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                        Confirmar Rechazo
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== RETURN TO MATERIALS MODAL ===== --}}
    @if ($showReturnModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closeReturnModal"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-orange-600 dark:bg-orange-700">
                    <h3 class="text-lg font-bold text-white">Regresar a Materiales</h3>
                    <button wire:click="closeReturnModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        La lista regresará al departamento de <strong class="text-gray-900 dark:text-white">Materiales</strong> para corrección. Se registrará el motivo en el historial.
                    </p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo del retorno <span class="text-red-500">*</span></label>
                        <textarea wire:model="returnReason" rows="4" placeholder="Indique qué debe corregir Materiales (mínimo 5 caracteres)..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"></textarea>
                        @error('returnReason')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeReturnModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="returnToMaterials"
                        class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors">
                        Confirmar Retorno
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== APPROVE AND SEND MODAL ===== --}}
    @if ($showApproveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="closeApproveModal"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 bg-green-600 dark:bg-green-700">
                    <h3 class="text-lg font-bold text-white">Aprobar y Enviar a Producción</h3>
                    <button wire:click="closeApproveModal" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="flex items-start gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-green-800 dark:text-green-300">Todos los lotes aprobados</p>
                            <p class="text-xs text-green-700 dark:text-green-400 mt-0.5">{{ $approvedCount }} de {{ $totalLots }} lotes han sido inspeccionados y aprobados.</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas de aprobación (opcional)</label>
                        <textarea wire:model="approveNotes" rows="3" placeholder="Observaciones para Producción..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeApproveModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="sendToProduction"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                        Enviar a Producción
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
