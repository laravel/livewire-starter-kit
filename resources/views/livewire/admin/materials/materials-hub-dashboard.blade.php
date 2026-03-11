<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Área de Materiales</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestión de lotes, kits y work orders para producción</p>
        </div>
    </div>

    <!-- Work Orders Overview -->
    <section>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Work Orders
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total WOs</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalWOs) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-700 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 border-2 border-green-200 dark:border-green-700 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-green-600 dark:text-green-400 mb-1">Activas</p>
                        <p class="text-2xl font-semibold text-green-700 dark:text-green-300">{{ number_format($activeWOs) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-700 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cerradas</p>
                        <p class="text-2xl font-semibold text-gray-700 dark:text-gray-300">{{ number_format($closedWOs) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Access -->
    <section>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            Acceso Rápido
        </h2>
        <a href="{{ route('admin.materials.manage') }}" wire:navigate
            class="group bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-lg transition-all block">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-xl bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-700 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-100 dark:group-hover:bg-amber-900/40 transition-colors">
                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Gestión de Materiales</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Work Orders activas, lotes y kits — solo muestra órdenes abiertas</p>
                    <div class="flex items-center gap-4 mt-4">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500 border-2 border-green-300 dark:border-green-600"></span>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $activeWOs }} WOs activas</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 border-2 border-yellow-300 dark:border-yellow-600"></span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $pendingLots }} lotes pendientes</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-orange-400 border-2 border-orange-300 dark:border-orange-600"></span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $kitsPreparing }} kits preparando</span>
                        </div>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-amber-500 transition-colors flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
    </section>

    <!-- Summary -->
    <section>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Resumen General
        </h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Lotes -->
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 text-center">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Lotes</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalLots) }}</div>
                <div class="flex items-center justify-center gap-2 mt-2 text-xs">
                    <span class="text-yellow-600 dark:text-yellow-400">{{ $pendingLots }} pend.</span>
                    <span class="text-blue-600 dark:text-blue-400">{{ $inProgressLots }} proc.</span>
                    <span class="text-green-600 dark:text-green-400">{{ $completedLots }} comp.</span>
                </div>
            </div>
            <!-- Kits Total -->
            <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 text-center">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Kits</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalKits) }}</div>
            </div>
            <!-- Kits Released -->
            <div class="bg-white dark:bg-gray-800 border-2 border-green-200 dark:border-green-700 rounded-lg p-4 text-center">
                <div class="text-xs text-green-600 dark:text-green-400 mb-1">Kits Liberados</div>
                <div class="text-2xl font-semibold text-green-700 dark:text-green-300">{{ number_format($kitsReleased) }}</div>
            </div>
            <!-- Kits In Assembly -->
            <div class="bg-white dark:bg-gray-800 border-2 border-indigo-200 dark:border-indigo-700 rounded-lg p-4 text-center">
                <div class="text-xs text-indigo-600 dark:text-indigo-400 mb-1">Kits en Ensamble</div>
                <div class="text-2xl font-semibold text-indigo-700 dark:text-indigo-300">{{ number_format($kitsInAssembly) }}</div>
            </div>
        </div>
    </section>

    <!-- Recent Sent Lists -->
    <section>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Listas de Envío Recientes
        </h2>
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            @if ($recentSentLists->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Work Orders</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($recentSentLists as $sl)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-4 text-blue-600 dark:text-blue-400 font-medium">#{{ $sl->id }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $sl->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right text-gray-700 dark:text-gray-300">{{ $sl->workOrders->count() }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('admin.sent-lists.show', $sl->id) }}" wire:navigate
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-800 rounded-md hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-4 text-base font-medium text-gray-900 dark:text-white">Sin listas de envío</p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Las listas aparecerán aquí cuando se creen.</p>
                </div>
            @endif
        </div>
    </section>
</div>
