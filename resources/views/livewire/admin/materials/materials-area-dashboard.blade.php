<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Área de Materiales</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestión de lotes y kits para producción</p>
        </div>
    </div>

    <!-- Pending Sent Lists -->
    @include('livewire.admin.sent-lists.partials.pending-lists-panel', [
        'pendingSentLists' => $pendingSentLists,
        'deptLabel'        => 'Materiales',
        'deptColor'        => 'blue',
    ])

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-800 border-2 border-blue-200 dark:border-blue-700 rounded-lg p-4">
            <div class="text-2xl font-semibold text-blue-600 dark:text-blue-400">{{ $stats['total_work_orders'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Work Orders</div>
        </div>
        <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="text-2xl font-semibold text-green-600 dark:text-green-400">{{ $stats['total_lots'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Lotes</div>
        </div>
        <div class="bg-white dark:bg-gray-800 border-2 border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
            <div class="text-2xl font-semibold text-yellow-600 dark:text-yellow-400">{{ $stats['pending_lots'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Lotes Pendientes</div>
        </div>
        <div class="bg-white dark:bg-gray-800 border-2 border-purple-200 dark:border-purple-700 rounded-lg p-4">
            <div class="text-2xl font-semibold text-purple-600 dark:text-purple-400">{{ $stats['total_kits'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Kits</div>
        </div>
        <div class="bg-white dark:bg-gray-800 border-2 border-orange-200 dark:border-orange-700 rounded-lg p-4">
            <div class="text-2xl font-semibold text-orange-600 dark:text-orange-400">{{ $stats['kits_preparing'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kits en Preparación</div>
        </div>
        <div class="bg-white dark:bg-gray-800 border-2 border-indigo-200 dark:border-indigo-700 rounded-lg p-4">
            <div class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400">{{ $stats['kits_pending_inspection'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pendientes Inspección</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg">
        <nav class="flex space-x-8 px-6" aria-label="Tabs">
            <button wire:click="switchView('work-orders')"
                class="@if ($viewMode === 'work-orders') border-indigo-500 text-indigo-600 dark:text-indigo-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                Work Orders
            </button>
            <button wire:click="switchView('lots')"
                class="@if ($viewMode === 'lots') border-indigo-500 text-indigo-600 dark:text-indigo-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                Lotes
            </button>
            <button wire:click="switchView('kits')"
                class="@if ($viewMode === 'kits') border-indigo-500 text-indigo-600 dark:text-indigo-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors">
                Kits
            </button>
        </nav>
    </div>

    <!-- Content Area -->
    <div class="mt-6">
        @if ($viewMode === 'work-orders')
            <livewire:admin.materials.dynamic-sent-list-view :key="'work-orders-view'" />
        @elseif($viewMode === 'lots')
            <livewire:admin.materials.lot-management :key="'lots-view'" />
        @elseif($viewMode === 'kits')
            <livewire:admin.materials.kit-management :key="'kits-view'" />
        @endif
    </div>
</div>
