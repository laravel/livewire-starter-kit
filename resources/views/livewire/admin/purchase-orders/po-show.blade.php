<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.purchase-orders.index') }}" class="inline-flex items-center justify-center w-10 h-10 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-md transition-colors" title="Volver">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Orden de compra: {{ $purchaseOrder->po_number }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detalle completo de la PO</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.purchase-orders.edit', $purchaseOrder) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors">Editar</a>
            <a href="{{ route('admin.purchase-orders.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-md transition-colors">Volver</a>
        </div>
    </div>

    @if (session('error'))
        <div class="p-3 rounded-md bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800">
            <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Información General</h2>
                    
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Número de PO</dt>
                            <dd class="text-sm sm:text-base text-gray-900 dark:text-white font-semibold">{{ $purchaseOrder->po_number }}</dd>
                        </div>
                        
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-3 rounded-lg">
                            <dt class="text-xs sm:text-sm font-medium text-indigo-600 dark:text-indigo-400 mb-1">WO (Cliente)</dt>
                            <dd class="text-base sm:text-xl text-indigo-700 dark:text-indigo-300 font-bold">{{ $purchaseOrder->wo ?? 'Sin asignar' }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Estado</dt>
                            <dd class="mt-1">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200',
                                        'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
                                        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
                                        'pending_correction' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-200',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$purchaseOrder->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $purchaseOrder->status_label }}
                                </span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Parte</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                <div class="font-medium">{{ $purchaseOrder->part->number }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $purchaseOrder->part->description }}</div>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Cantidad</dt>
                            <dd class="text-sm sm:text-base text-gray-900 dark:text-white font-semibold">{{ number_format($purchaseOrder->quantity) }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Precio Unitario</dt>
                            <dd class="text-sm sm:text-base text-gray-900 dark:text-white font-semibold">${{ number_format($purchaseOrder->unit_price, 4) }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total</dt>
                            <dd class="text-sm sm:text-base text-gray-900 dark:text-white font-semibold">${{ number_format($purchaseOrder->quantity * $purchaseOrder->unit_price, 2) }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de PO</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $purchaseOrder->po_date->format('d/m/Y') }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha de Entrega</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $purchaseOrder->due_date->format('d/m/Y') }}</dd>
                        </div>
                    </dl>

                    @if($purchaseOrder->comments)
                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Comentarios</dt>
                            <dd class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $purchaseOrder->comments }}</dd>
                        </div>
                    @endif

                    @if($purchaseOrder->pdf_path)
                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Documento PDF</dt>
                            <dd>
                                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                            </svg>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">{{ basename($purchaseOrder->pdf_path) }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Documento de la orden de compra</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ Storage::url($purchaseOrder->pdf_path) }}" target="_blank"
                                                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-sm font-medium rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Ver
                                            </a>
                                            <a href="{{ Storage::url($purchaseOrder->pdf_path) }}" download
                                                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-700 dark:text-green-300 text-sm font-medium rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                </svg>
                                                Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Side Panel -->
            <div class="space-y-6">
                <!-- Price Validation Card -->
                <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <div class="p-4 sm:p-6">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Validación de Precio</h2>
                        
                        <div class="p-4 rounded-lg {{ $price_valid ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800' }}">
                            <div class="flex items-start gap-2">
                                @if($price_valid)
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-orange-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm {{ $price_valid ? 'text-green-700 dark:text-green-300' : 'text-orange-700 dark:text-orange-300' }} font-medium">
                                        {{ $price_message }}
                                    </span>
                                    @if($expected_price !== null)
                                        <p class="mt-2 text-xs {{ $price_valid ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                            Precio esperado: ${{ number_format($expected_price, 4) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                @if($purchaseOrder->status === 'pending')
                    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="p-4 sm:p-6">
                            <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Acciones</h2>
                            
                            <div class="space-y-3">
                                <button wire:click="approve"
                                    class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Aprobar PO
                                </button>
                                
                                <button wire:click="reject"
                                    class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Rechazar PO
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Work Order Card -->
                @if($purchaseOrder->workOrder)
                    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="p-4 sm:p-6">
                            <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Work Order Asociada</h2>
                            
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                <p class="text-sm text-blue-700 dark:text-blue-300 mb-1">
                                    <strong>WO #:</strong> {{ $purchaseOrder->workOrder->wo_number }}
                                </p>
                                <p class="text-sm text-blue-600 dark:text-blue-400">
                                    <strong>Estado:</strong> {{ $purchaseOrder->workOrder->status->name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Timestamps Card -->
                <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <div class="p-4 sm:p-6">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Información del Sistema</h2>
                        
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Creado</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $purchaseOrder->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Actualizado</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $purchaseOrder->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
</div>
