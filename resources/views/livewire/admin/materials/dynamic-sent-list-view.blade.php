<div class="space-y-4">
    {{-- Search and Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex-1 max-w-md">
            <flux:input
                wire:model.live.debounce.300ms="searchTerm"
                placeholder="Buscar por WO, PO, cliente o parte..."
                icon="magnifying-glass"
            />
        </div>

        <div class="flex gap-2">
            <select wire:model.live="filterStatus" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                <option value="pending">Pendiente</option>
                <option value="in_progress">En Progreso</option>
                <option value="completed">Completado</option>
            </select>

            @if($searchTerm || $filterStatus)
                <flux:button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Work Orders Table --}}
    @if($workOrders->isEmpty())
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay Work Orders con lotes</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Las Work Orders aparecerán cuando tengan lotes asignados.</p>
        </div>
    @else
        <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">WO #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PO</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Parte</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lotes</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kits</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($workOrders as $workOrder)
                            @php
                                $po = $workOrder->purchaseOrder;
                                $part = $po->part ?? null;
                                $lotsCount = $workOrder->lots->count();
                                $kitsCount = $workOrder->kits->count();
                                $pendingLots = $workOrder->lots->where('status', 'pending')->count();
                                $completedLots = $workOrder->lots->where('status', 'completed')->count();
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                        {{ $po->wo ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $po->po_number ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $part->number ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $part->description ?? '' }}">
                                    {{ $part->description ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-white">
                                    {{ number_format($po->quantity ?? 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $lotsCount }}
                                    </span>
                                    @if($pendingLots > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 ml-1">
                                            {{ $pendingLots }} pend.
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ $kitsCount }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($completedLots === $lotsCount && $lotsCount > 0)
                                        <flux:badge color="green">Completo</flux:badge>
                                    @elseif($completedLots > 0)
                                        <flux:badge color="yellow">En Progreso</flux:badge>
                                    @else
                                        <flux:badge color="zinc">Pendiente</flux:badge>
                                    @endif
                                </td>
                            </tr>

                            {{-- Expandable Lots Row --}}
                            @if($workOrder->lots->isNotEmpty())
                                <tr class="bg-gray-50 dark:bg-gray-900/50">
                                    <td colspan="8" class="px-6 py-3">
                                        <div class="pl-4 border-l-2 border-blue-300 dark:border-blue-600">
                                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Lotes de esta WO:</div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                                @foreach($workOrder->lots as $lot)
                                                    <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">
                                                        <div>
                                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $lot->lot_number }}</span>
                                                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">{{ number_format($lot->quantity) }} pcs</span>
                                                        </div>
                                                        @php
                                                            $lotStatusColor = match($lot->status) {
                                                                'pending' => 'zinc',
                                                                'in_progress' => 'yellow',
                                                                'completed' => 'green',
                                                                default => 'zinc',
                                                            };
                                                        @endphp
                                                        <flux:badge :color="$lotStatusColor" size="sm">
                                                            {{ ucfirst($lot->status) }}
                                                        </flux:badge>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $workOrders->links() }}
        </div>
    @endif
</div>
