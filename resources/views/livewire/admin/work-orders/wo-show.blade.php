<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Work Order: {{ $workOrder->wo_number }}</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Detalle completo de la WO
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="{{ route('admin.work-orders.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                    <a href="{{ route('admin.work-orders.edit', $workOrder) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info Card -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información General</h2>
                    
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de WO</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $workOrder->wo_number }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                            <dd class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full text-white" style="background-color: {{ $workOrder->status->color }};">
                                    {{ $workOrder->status->name }}
                                </span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Purchase Order</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <a href="{{ route('admin.purchase-orders.show', $workOrder->purchaseOrder) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                    {{ $workOrder->purchaseOrder->po_number }}
                                </a>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parte</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $workOrder->purchaseOrder->part->number }}
                                <span class="text-gray-500 dark:text-gray-400">- {{ $workOrder->purchaseOrder->part->description }}</span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cantidad Original</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ number_format($workOrder->original_quantity) }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Piezas Enviadas</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ number_format($workOrder->sent_pieces) }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cantidad Pendiente</dt>
                            <dd class="mt-1 text-sm {{ $workOrder->pending_quantity > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }} font-semibold">
                                {{ number_format($workOrder->pending_quantity) }}
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Apertura</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->opened_date->format('d/m/Y') }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Programada de Envío</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->scheduled_send_date?->format('d/m/Y') ?? 'No definida' }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Real de Envío</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->actual_send_date?->format('d/m/Y') ?? 'No enviado' }}</dd>
                        </div>
                        
                        @if($workOrder->eq)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Equipo (EQ)</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->eq }}</dd>
                        </div>
                        @endif
                        
                        @if($workOrder->pr)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Personal (PR)</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->pr }}</dd>
                        </div>
                        @endif
                    </dl>

                    @if($workOrder->comments)
                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Comentarios</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $workOrder->comments }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Side Panel -->
            <div class="space-y-6">
                <!-- Progress Card -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Progreso</h2>
                        
                        @php
                            $progress = $workOrder->original_quantity > 0 
                                ? round(($workOrder->sent_pieces / $workOrder->original_quantity) * 100, 1) 
                                : 0;
                        @endphp
                        
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Completado</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                            <div class="h-3 rounded-full {{ $progress >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ min($progress, 100) }}%"></div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($workOrder->sent_pieces) }} / {{ number_format($workOrder->original_quantity) }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">piezas enviadas</p>
                        </div>
                    </div>
                </div>

                <!-- Timestamps Card -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información del Sistema</h2>
                        
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Creado</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $workOrder->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Actualizado</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $workOrder->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Log -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Historial de Cambios de Estado</h2>
                
                @if($workOrder->statusLogs->count() > 0)
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($workOrder->statusLogs->sortByDesc('created_at') as $log)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800" style="background-color: {{ $log->toStatus->color ?? '#6B7280' }};">
                                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        @if($log->fromStatus)
                                                            <span class="font-medium" style="color: {{ $log->fromStatus->color }};">{{ $log->fromStatus->name }}</span>
                                                            →
                                                        @else
                                                            Creado como
                                                        @endif
                                                        <span class="font-medium" style="color: {{ $log->toStatus->color }};">{{ $log->toStatus->name }}</span>
                                                        @if($log->user)
                                                            por <span class="font-medium text-gray-900 dark:text-white">{{ $log->user->name }}</span>
                                                        @endif
                                                    </p>
                                                    @if($log->comments)
                                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $log->comments }}</p>
                                                    @endif
                                                </div>
                                                <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay cambios de estado registrados.</p>
                @endif
            </div>
        </div>
    </div>
</div>
