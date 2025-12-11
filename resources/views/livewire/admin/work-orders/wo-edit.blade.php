<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Work Order: {{ $workOrder->wo_number }}</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Actualizar información de la WO
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.work-orders.show', $workOrder) }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        <strong>PO:</strong> {{ $workOrder->purchaseOrder->po_number }} |
                        <strong>Parte:</strong> {{ $workOrder->purchaseOrder->part->number }} - {{ $workOrder->purchaseOrder->part->description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <form wire:submit="save" class="p-6 space-y-6">
                <!-- Status -->
                <div>
                    <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                    <select
                        wire:model="status_id"
                        id="status_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                    @error('status_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Status Change Comments (only show if status is different) -->
                @if($status_id != $workOrder->status_id)
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <label for="status_change_comments" class="block text-sm font-medium text-yellow-700 dark:text-yellow-300">
                            Comentarios del cambio de estado (opcional)
                        </label>
                        <textarea
                            wire:model="status_change_comments"
                            id="status_change_comments"
                            rows="2"
                            class="mt-1 block w-full px-3 py-2 border border-yellow-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:border-yellow-600 dark:text-white"
                            placeholder="Razón del cambio de estado..."
                        ></textarea>
                    </div>
                @endif

                <!-- Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="scheduled_send_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Programada de Envío</label>
                        <input
                            wire:model="scheduled_send_date"
                            type="date"
                            id="scheduled_send_date"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                        @error('scheduled_send_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="actual_send_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha Real de Envío</label>
                        <input
                            wire:model="actual_send_date"
                            type="date"
                            id="actual_send_date"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                        @error('actual_send_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Equipment and Personnel -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="eq" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Equipo (EQ)</label>
                        <input
                            wire:model="eq"
                            type="text"
                            id="eq"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Equipo asignado..."
                        >
                        @error('eq') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="pr" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Personal (PR)</label>
                        <input
                            wire:model="pr"
                            type="text"
                            id="pr"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Personal asignado..."
                        >
                        @error('pr') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Comments -->
                <div>
                    <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Comentarios</label>
                    <textarea
                        wire:model="comments"
                        id="comments"
                        rows="3"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Comentarios adicionales..."
                    ></textarea>
                    @error('comments') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.work-orders.show', $workOrder) }}"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
                        Cancelar
                    </a>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm"
                    >
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
