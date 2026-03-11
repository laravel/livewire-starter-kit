<x-layouts.admin>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4 w-full">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Editar lista #{{ $sentList->id }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cambiar estado de la lista</p>
            </div>
            <a href="{{ route('admin.sent-lists.show', $sentList) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm flex-shrink-0 ml-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('error'))
            <div class="rounded-lg border-2 border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3">
                <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Cambiar estado de la lista</h3>

                {{-- Resumen --}}
                <div class="mb-6 p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">PO / Partes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if($sentList->purchaseOrders && $sentList->purchaseOrders->count() > 0)
                                    @if($sentList->purchaseOrders->count() === 1)
                                        {{ $sentList->purchaseOrders->first()->po_number }}
                                    @else
                                        {{ $sentList->purchaseOrders->count() }} POs
                                    @endif
                                @else
                                    N/A
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado actual</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $sentList->status === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                    {{ $sentList->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                                    {{ $sentList->status === 'canceled' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                    {{ $sentList->status_label }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Período</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $sentList->start_date->format('d/m/Y') }} – {{ $sentList->end_date->format('d/m/Y') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Work Orders</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $sentList->workOrders->count() }}</dd>
                        </div>
                    </dl>
                </div>

                <form action="{{ route('admin.sent-lists.update', $sentList) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nuevo estado</label>
                        <select name="status" id="status"
                            class="block w-full px-3 py-2 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="pending" {{ $sentList->status === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="confirmed" {{ $sentList->status === 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                            <option value="canceled" {{ $sentList->status === 'canceled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('admin.sent-lists.show', $sentList) }}"
                            class="inline-flex items-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
