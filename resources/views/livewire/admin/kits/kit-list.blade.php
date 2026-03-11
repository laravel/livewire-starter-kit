<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Kits</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestión de kits de producción</p>
        </div>
        <a href="{{ route('admin.kits.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nuevo Kit
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        @foreach($statuses as $status => $label)
            @php
                $borderClass = match($status) {
                    'preparing' => 'border-yellow-200 dark:border-yellow-700',
                    'ready' => 'border-blue-200 dark:border-blue-700',
                    'released' => 'border-green-200 dark:border-green-700',
                    'in_assembly' => 'border-orange-200 dark:border-orange-700',
                    'rejected' => 'border-red-200 dark:border-red-700',
                    default => 'border-gray-200 dark:border-gray-700',
                };
                $bgClass = match($status) {
                    'preparing' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-700',
                    'ready' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700',
                    'released' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700',
                    'in_assembly' => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-700',
                    'rejected' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700',
                    default => 'bg-gray-50 dark:bg-gray-900/20 border-gray-200 dark:border-gray-700',
                };
                $dotClass = match($status) {
                    'preparing' => 'bg-yellow-500 border-yellow-300 dark:border-yellow-600',
                    'ready' => 'bg-blue-500 border-blue-300 dark:border-blue-600',
                    'released' => 'bg-green-500 border-green-300 dark:border-green-600',
                    'in_assembly' => 'bg-orange-500 border-orange-300 dark:border-orange-600',
                    'rejected' => 'bg-red-500 border-red-300 dark:border-red-600',
                    default => 'bg-gray-500 border-gray-300 dark:border-gray-600',
                };
            @endphp
            <div class="bg-white dark:bg-gray-800 border-2 {{ $borderClass }} rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-lg {{ $bgClass }} border-2 flex items-center justify-center">
                            <div class="w-3 h-3 rounded-full {{ $dotClass }} border-2"></div>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $label }}</p>
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ \App\Models\Kit::where('status', $status)->count() }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <div class="flex flex-col sm:flex-row gap-4 mb-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por número de kit o WO..."
                    class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="sm:w-48">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                <select wire:model.live="filterStatus"
                    class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-40">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Por página</label>
                <select wire:model.live="perPage"
                    class="w-full px-4 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        @if (session('message'))
            <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-md mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-md mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('kit_number')" class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                # Kit
                                @if ($sortField === 'kit_number')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Work Order</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Parte</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('status')" class="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
                                Estado
                                @if ($sortField === 'status')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Lotes</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Validado</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($kits as $kit)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $kit->kit_number }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('admin.work-orders.show', $kit->workOrder) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                    {{ $kit->workOrder->purchaseOrder->wo ?? 'N/A' }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $kit->workOrder->purchaseOrder->part->number }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'preparing' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 border-2 border-yellow-200 dark:border-yellow-700',
                                        'ready' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-2 border-blue-200 dark:border-blue-700',
                                        'released' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border-2 border-green-200 dark:border-green-700',
                                        'in_assembly' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 border-2 border-orange-200 dark:border-orange-700',
                                        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-2 border-red-200 dark:border-red-700',
                                    ];
                                @endphp
                                <button wire:click="openStatusModal({{ $kit->id }})" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$kit->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border-2 border-gray-200 dark:border-gray-600' }} hover:opacity-80 cursor-pointer transition-opacity">
                                    {{ $kit->status_label }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($kit->lots->count() > 0)
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $kit->lots->count() }} lote(s)
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $kit->lots->pluck('lot_number')->take(2)->join(', ') }}{{ $kit->lots->count() > 2 ? '...' : '' }}
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">Sin lotes</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($kit->validated)
                                    <span class="text-green-600 dark:text-green-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.kits.show', $kit) }}" class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 border-2 border-transparent hover:border-indigo-300 dark:hover:border-indigo-700 rounded-md transition-colors" title="Ver">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <button wire:click="openStatusModal({{ $kit->id }})" class="inline-flex items-center justify-center w-8 h-8 text-teal-600 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/20 border-2 border-transparent hover:border-teal-300 dark:hover:border-teal-700 rounded-md transition-colors" title="Cambiar Estado">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDeletion({{ $kit->id }})" class="inline-flex items-center justify-center w-8 h-8 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 border-2 border-transparent hover:border-red-300 dark:hover:border-red-700 rounded-md transition-colors" title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p class="mt-4 text-base font-medium text-gray-900 dark:text-white">No se encontraron kits</p>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Los kits aparecerán aquí cuando se creen</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kits->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                {{ $kits->links() }}
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    @if($confirmingDeletion)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-900/50"></div>
                </div>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-2 border-gray-200 dark:border-gray-700">
                    <div class="px-6 pt-6 pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 border-2 border-red-200 dark:border-red-700 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">Confirmar eliminación</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">¿Está seguro de eliminar este kit?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 sm:flex sm:flex-row-reverse">
                        <button wire:click="delete" class="w-full inline-flex justify-center items-center gap-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Eliminar
                        </button>
                        <button wire:click="$set('confirmingDeletion', false)" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal para Cambiar Estado del Kit --}}
    @if($showStatusModal)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" wire:click="closeStatusModal">
                    <div class="absolute inset-0 bg-gray-900/50"></div>
                </div>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-2 border-gray-200 dark:border-gray-700">
                    <div class="px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                                Cambiar Estado del Kit
                            </h3>
                            <button wire:click="closeStatusModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Seleccione el nuevo estado para el kit
                        </p>

                        <div class="grid grid-cols-2 gap-3">
                            {{-- En Preparación --}}
                            <button wire:click="setNewStatus('preparing')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'preparing' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                <div class="w-8 h-8 rounded-full bg-yellow-400 border-2 border-yellow-300 dark:border-yellow-600 mb-2"></div>
                                <span class="text-sm font-medium {{ $newStatus === 'preparing' ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    En Preparación
                                </span>
                            </button>

                            {{-- Listo --}}
                            <button wire:click="setNewStatus('ready')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'ready' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                <div class="w-8 h-8 rounded-full bg-blue-500 border-2 border-blue-300 dark:border-blue-600 mb-2"></div>
                                <span class="text-sm font-medium {{ $newStatus === 'ready' ? 'text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    Listo
                                </span>
                            </button>

                            {{-- Liberado (Aprobado) --}}
                            <button wire:click="setNewStatus('released')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'released' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                <div class="w-8 h-8 rounded-full bg-green-500 border-2 border-green-300 dark:border-green-600 mb-2 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium {{ $newStatus === 'released' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    Liberado
                                </span>
                            </button>

                            {{-- En Ensamble --}}
                            <button wire:click="setNewStatus('in_assembly')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all {{ $newStatus === 'in_assembly' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                <div class="w-8 h-8 rounded-full bg-orange-500 border-2 border-orange-300 dark:border-orange-600 mb-2 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium {{ $newStatus === 'in_assembly' ? 'text-orange-700 dark:text-orange-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    En Ensamble
                                </span>
                            </button>

                            {{-- Rechazado --}}
                            <button wire:click="setNewStatus('rejected')"
                                class="flex flex-col items-center p-4 border-2 rounded-lg transition-all col-span-2 {{ $newStatus === 'rejected' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                <div class="w-8 h-8 rounded-full bg-red-500 border-2 border-red-300 dark:border-red-600 mb-2 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium {{ $newStatus === 'rejected' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }}">
                                    Rechazado
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 sm:flex sm:flex-row-reverse">
                        <button wire:click="updateKitStatus" class="w-full inline-flex justify-center items-center gap-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Guardar Cambios
                        </button>
                        <button wire:click="closeStatusModal" class="mt-3 w-full inline-flex justify-center rounded-md border-2 border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
