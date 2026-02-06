<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Producción - Pesadas</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Registro de pesadas por lote y kit
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="{{ route('admin.sent-lists.display') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver a Display
                    </a>
                    <button wire:click="openCreateModal"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nueva Pesada
                    </button>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('message'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-green-800 dark:text-green-200 text-sm font-medium">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-800 dark:text-red-200 text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Search -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input wire:model.live.debounce.300ms="search" type="text"
                            placeholder="Buscar por lote, WO o parte..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('id')">
                                ID
                                @if($sortField === 'id')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Lote</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                WO</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Parte</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Kit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Cantidad</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Pz Buenas</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Pz Malas</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('weighed_at')">
                                Fecha Pesada
                                @if($sortField === 'weighed_at')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Pesó</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($weighings as $weighing)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $weighing->id }}</td>
                                <td class="px-4 py-3 text-sm text-blue-600 dark:text-blue-400 font-medium">
                                    {{ $weighing->lot->lot_number ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $weighing->lot->workOrder->purchaseOrder->wo ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $weighing->lot->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $weighing->kit->kit_number ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">
                                    {{ number_format($weighing->quantity) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-green-600 dark:text-green-400">
                                    {{ number_format($weighing->good_pieces) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-red-600 dark:text-red-400">
                                    {{ number_format($weighing->bad_pieces) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-gray-700 dark:text-gray-300">
                                    {{ $weighing->weighed_at->format('m/d/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $weighing->weighedBy->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        {{-- Ver --}}
                                        <button wire:click="openDetailModal({{ $weighing->id }})"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                            title="Ver detalle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        {{-- Editar --}}
                                        <button wire:click="openEditModal({{ $weighing->id }})"
                                            class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300"
                                            title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        {{-- Eliminar --}}
                                        <button wire:click="confirmDeletion({{ $weighing->id }})"
                                            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                                            title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-lg font-medium">No hay pesadas registradas</p>
                                    <p class="mt-1">Haz clic en "Nueva Pesada" para registrar una.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($weighings->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $weighings->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================= --}}
    {{-- MODAL CREAR / EDITAR PESADA --}}
    {{-- ============================================= --}}
    @if ($showFormModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="weighing-form-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeFormModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-indigo-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="weighing-form-title" class="text-lg font-semibold text-white">
                                    {{ $editingWeighingId ? 'Editar Pesada' : 'Nueva Pesada' }}
                                </h3>
                                <p class="text-sm text-indigo-100 mt-1">
                                    {{ $editingWeighingId ? 'Modificar registro de pesada' : 'Registrar nueva pesada de producción' }}
                                </p>
                            </div>
                            <button wire:click="closeFormModal" class="text-white hover:text-indigo-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-5">
                        {{-- Lote --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lote *</label>
                            <select wire:model.live="selectedLotId"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Seleccionar lote...</option>
                                @foreach ($lots as $lot)
                                    <option value="{{ $lot->id }}">
                                        {{ $lot->lot_number }} - WO: {{ $lot->workOrder->purchaseOrder->wo ?? 'N/A' }} | {{ $lot->workOrder->purchaseOrder->part->number ?? '' }} ({{ number_format($lot->quantity) }} pz)
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedLotId')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Kit (opcional) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kit (opcional)</label>
                            <select wire:model="selectedKitId"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                {{ count($kits) === 0 ? 'disabled' : '' }}>
                                <option value="">Sin kit</option>
                                @foreach ($kits as $kit)
                                    <option value="{{ $kit->id }}">{{ $kit->kit_number }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Cantidad (solo visual) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad del Lote</label>
                            <div class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm rounded-lg font-semibold">
                                {{ number_format($formQuantity) }} piezas
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Campo informativo - no editable</p>
                        </div>

                        {{-- Piezas buenas y malas --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Buenas *</label>
                                <input wire:model="formGoodPieces" type="number" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    placeholder="0">
                                @error('formGoodPieces')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piezas Malas *</label>
                                <input wire:model="formBadPieces" type="number" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    placeholder="0">
                                @error('formBadPieces')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Fecha y hora --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y Hora de Pesada *</label>
                            <input wire:model="formWeighedAt" type="datetime-local"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('formWeighedAt')
                                <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Comentarios --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comentarios</label>
                            <textarea wire:model="formComments" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Observaciones (opcional)..."></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button wire:click="closeFormModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="save"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                            {{ $editingWeighingId ? 'Actualizar' : 'Registrar' }} Pesada
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================= --}}
    {{-- MODAL VER DETALLE --}}
    {{-- ============================================= --}}
    @if ($showDetailModal && $detailWeighing)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="weighing-detail-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeDetailModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="weighing-detail-title" class="text-lg font-semibold text-white">Detalle de Pesada #{{ $detailWeighing->id }}</h3>
                                <p class="text-sm text-gray-300 mt-1">
                                    Lote: {{ $detailWeighing->lot->lot_number ?? 'N/A' }}
                                </p>
                            </div>
                            <button wire:click="closeDetailModal" class="text-white hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block">WO:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    {{ $detailWeighing->lot->workOrder->purchaseOrder->wo ?? 'N/A' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block">Parte:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    {{ $detailWeighing->lot->workOrder->purchaseOrder->part->number ?? 'N/A' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block">Lote:</span>
                                <span class="text-blue-600 dark:text-blue-400 font-medium">
                                    {{ $detailWeighing->lot->lot_number ?? 'N/A' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block">Kit:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    {{ $detailWeighing->kit->kit_number ?? 'Sin kit' }}
                                </span>
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Cantidad</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($detailWeighing->quantity) }}</p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg text-center">
                                <p class="text-xs text-green-600 dark:text-green-400">Pz Buenas</p>
                                <p class="text-lg font-bold text-green-700 dark:text-green-300">{{ number_format($detailWeighing->good_pieces) }}</p>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg text-center">
                                <p class="text-xs text-red-600 dark:text-red-400">Pz Malas</p>
                                <p class="text-lg font-bold text-red-700 dark:text-red-300">{{ number_format($detailWeighing->bad_pieces) }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block">Fecha de Pesada:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    {{ $detailWeighing->weighed_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block">Pesó:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    {{ $detailWeighing->weighedBy->name ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        @if ($detailWeighing->comments)
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block text-sm">Comentarios:</span>
                                <p class="text-gray-900 dark:text-white text-sm mt-1 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                                    {{ $detailWeighing->comments }}
                                </p>
                            </div>
                        @endif

                        <div class="text-xs text-gray-400 dark:text-gray-500">
                            Creado: {{ $detailWeighing->created_at->format('d/m/Y H:i') }} |
                            Actualizado: {{ $detailWeighing->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
                        <button wire:click="closeDetailModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================= --}}
    {{-- MODAL CONFIRMAR ELIMINACIÓN --}}
    {{-- ============================================= --}}
    @if ($confirmingDeletion)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="cancelDeletion"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Eliminar Pesada</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    ¿Estás seguro de que deseas eliminar esta pesada? Esta acción no se puede deshacer.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3">
                        <button wire:click="cancelDeletion"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="delete"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
