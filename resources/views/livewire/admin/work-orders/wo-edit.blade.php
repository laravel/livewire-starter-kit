<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.work-orders.index') }}" class="inline-flex items-center justify-center w-10 h-10 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-md transition-colors" title="Volver">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Editar WO: {{ $workOrder->purchaseOrder->wo ?? $workOrder->wo_number }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Actualizar información de la orden</p>
        </div>
    </div>

        <!-- WO Cliente Card -->
        @if($workOrder->purchaseOrder->wo)
        <div class="mb-6 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-200 dark:border-indigo-800 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-indigo-500 dark:text-indigo-400 uppercase">WO Cliente</p>
                    <p class="text-xl font-bold text-indigo-700 dark:text-indigo-300">{{ $workOrder->purchaseOrder->wo }}</p>
                </div>
            </div>
        </div>
        @endif

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

                <!-- Numero WO Externo (FPL-10) -->
                <div>
                    <label for="external_wo_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Numero WO Externo
                        <span class="ml-1 text-xs font-normal text-gray-400">(Packing Slip FPL-10)</span>
                    </label>
                    <input
                        wire:model="external_wo_number"
                        type="text"
                        id="external_wo_number"
                        maxlength="20"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white font-mono"
                        placeholder="Ej: 1980231"
                    >
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Numero externo de 7 digitos para el Packing Slip FPL-10 (ej: 1980231). Sin este numero el lote no puede incluirse en un PS.
                    </p>
                    @error('external_wo_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

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
