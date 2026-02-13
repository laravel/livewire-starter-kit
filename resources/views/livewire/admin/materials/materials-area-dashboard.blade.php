<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Área de Materiales</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestión de lotes y kits para producción</p>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_work_orders'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Work Orders</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['total_lots'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Total Lotes</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending_lots'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Lotes Pendientes</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['total_kits'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Total Kits</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['kits_preparing'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Kits en Preparación</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['kits_pending_inspection'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Pendientes Inspección</div>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="switchView('work-orders')"
                class="@if ($viewMode === 'work-orders') border-indigo-500 text-indigo-600 dark:text-indigo-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                Work Orders
            </button>
            <button wire:click="switchView('lots')"
                class="@if ($viewMode === 'lots') border-indigo-500 text-indigo-600 dark:text-indigo-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                Lotes
            </button>
            <button wire:click="switchView('kits')"
                class="@if ($viewMode === 'kits') border-indigo-500 text-indigo-600 dark:text-indigo-400 @else border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300 @endif whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                Kits
            </button>
        </nav>
    </div>

    {{-- Content Area --}}
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
