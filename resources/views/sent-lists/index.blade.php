<x-layouts.admin>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Listas Preliminares') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Listas generadas desde el wizard de capacidad</p>
            </div>
            <a href="{{ route('admin.capacity.wizard') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Nueva Lista
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Flash --}}
        @if (session('success'))
            <div class="rounded-lg border-2 border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3" role="alert">
                <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border-2 border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3" role="alert">
                <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $sentLists->total() }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Pendientes</div>
                <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $sentLists->where('status', 'pending')->count() }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Confirmadas</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $sentLists->where('status', 'confirmed')->count() }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Canceladas</div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $sentLists->where('status', 'canceled')->count() }}</div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            @if ($sentLists->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">PO</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Parte</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Período</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Personas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Capacidad</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Depto.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach ($sentLists as $sentList)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">#{{ $sentList->id }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        @if($sentList->purchaseOrders && $sentList->purchaseOrders->count() > 0)
                                            @if($sentList->purchaseOrders->count() === 1)
                                                {{ $sentList->purchaseOrders->first()->po_number }}
                                            @else
                                                <div class="flex flex-col space-y-0.5">
                                                    @foreach($sentList->purchaseOrders->take(2) as $po)
                                                        <span class="text-xs">{{ $po->po_number }}</span>
                                                    @endforeach
                                                    @if($sentList->purchaseOrders->count() > 2)
                                                        <span class="text-xs text-gray-500">+{{ $sentList->purchaseOrders->count() - 2 }} más</span>
                                                    @endif
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        @if($sentList->purchaseOrders && $sentList->purchaseOrders->count() > 0)
                                            @php $uniqueParts = $sentList->purchaseOrders->pluck('part.number')->unique()->filter(); @endphp
                                            @if($uniqueParts->count() === 1)
                                                <span class="font-medium">{{ $uniqueParts->first() }}</span>
                                            @else
                                                <div class="flex flex-col space-y-0.5">
                                                    @foreach($uniqueParts->take(2) as $partNumber)
                                                        <span class="text-xs font-medium">{{ $partNumber }}</span>
                                                    @endforeach
                                                    @if($uniqueParts->count() > 2)
                                                        <span class="text-xs text-gray-500">+{{ $uniqueParts->count() - 2 }} más</span>
                                                    @endif
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        @if($sentList->start_date && $sentList->end_date)
                                            <span class="text-xs">{{ $sentList->start_date->format('d/m/Y') }} – {{ $sentList->end_date->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $sentList->num_persons }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="flex flex-col text-xs">
                                            <span class="text-gray-500">Disp: {{ number_format($sentList->total_available_hours, 2) }}h</span>
                                            <span class="text-blue-600 dark:text-blue-400">Usado: {{ number_format($sentList->used_hours, 2) }}h</span>
                                            <span class="{{ $sentList->remaining_hours >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                Rest: {{ number_format($sentList->remaining_hours, 2) }}h
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            {{ $sentList->department_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $sentList->status === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                            {{ $sentList->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                                            {{ $sentList->status === 'canceled' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                            {{ $sentList->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.sent-lists.show', $sentList) }}"
                                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition"
                                                title="Ver">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            @if ($sentList->isPending())
                                                <a href="{{ route('admin.sent-lists.edit', $sentList) }}"
                                                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition"
                                                    title="Editar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                <form action="{{ route('admin.sent-lists.destroy', $sentList) }}" method="POST" class="inline"
                                                    onsubmit="return confirm('¿Eliminar esta lista preliminar?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Eliminar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $sentLists->links() }}
                </div>
            @else
                <div class="text-center py-12 px-4">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-base font-medium text-gray-900 dark:text-white">No hay listas preliminares</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crea una desde el wizard de capacidad.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.capacity.wizard') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Ir al Wizard de Capacidad
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
