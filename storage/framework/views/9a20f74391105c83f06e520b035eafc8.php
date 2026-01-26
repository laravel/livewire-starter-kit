<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">ID: <?php echo e($workOrder->wo_number); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workOrder->purchaseOrder?->wo): ?>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">WO: <?php echo e($workOrder->purchaseOrder->wo); ?></h1>
                    <?php else: ?>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($workOrder->wo_number); ?></h1>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Detalle de la orden de trabajo
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="<?php echo e(route('admin.work-orders.index')); ?>"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                    <a href="<?php echo e(route('admin.work-orders.edit', $workOrder)); ?>"
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ID (Interno)</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold"><?php echo e($workOrder->wo_number); ?></dd>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workOrder->purchaseOrder?->wo): ?>
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-3 rounded-lg -m-1">
                            <dt class="text-sm font-medium text-indigo-600 dark:text-indigo-400">WO (Cliente)</dt>
                            <dd class="mt-1 text-xl text-indigo-700 dark:text-indigo-300 font-bold"><?php echo e($workOrder->purchaseOrder->wo); ?></dd>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                            <dd class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full text-white" style="background-color: <?php echo e($workOrder->status->color); ?>;">
                                    <?php echo e($workOrder->status->name); ?>

                                </span>
                            </dd>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workOrder->purchaseOrder): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Purchase Order</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <a href="<?php echo e(route('admin.purchase-orders.show', $workOrder->purchaseOrder)); ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                    <?php echo e($workOrder->purchaseOrder->po_number); ?>

                                </a>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parte</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <?php echo e($workOrder->purchaseOrder->part->number ?? 'N/A'); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workOrder->purchaseOrder->part): ?>
                                <span class="text-gray-500 dark:text-gray-400">- <?php echo e($workOrder->purchaseOrder->part->description); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </dd>
                        </div>
                        <?php else: ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Purchase Order</dt>
                            <dd class="mt-1 text-sm text-gray-500 dark:text-gray-400">No asociado</dd>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cantidad Original</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold"><?php echo e(number_format($workOrder->original_quantity)); ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Piezas Enviadas</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold"><?php echo e(number_format($workOrder->sent_pieces)); ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cantidad Pendiente</dt>
                            <dd class="mt-1 text-sm <?php echo e($workOrder->pending_quantity > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400'); ?> font-semibold">
                                <?php echo e(number_format($workOrder->pending_quantity)); ?>

                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Apertura</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($workOrder->opened_date->format('d/m/Y')); ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Programada de Envío</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($workOrder->scheduled_send_date?->format('d/m/Y') ?? 'No definida'); ?></dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Real de Envío</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($workOrder->actual_send_date?->format('d/m/Y') ?? 'No enviado'); ?></dd>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workOrder->eq): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Equipo (EQ)</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($workOrder->eq); ?></dd>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workOrder->pr): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Personal (PR)</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($workOrder->pr); ?></dd>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </dl>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workOrder->comments): ?>
                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Comentarios</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($workOrder->comments); ?></dd>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <!-- Side Panel -->
            <div class="space-y-6">
                <!-- Progress Card -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Progreso</h2>
                        
                        <?php
                            $progress = $workOrder->original_quantity > 0 
                                ? round(($workOrder->sent_pieces / $workOrder->original_quantity) * 100, 1) 
                                : 0;
                        ?>
                        
                        <div class="mb-2 flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Completado</span>
                            <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($progress); ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                            <div class="h-3 rounded-full <?php echo e($progress >= 100 ? 'bg-green-500' : 'bg-blue-500'); ?>" style="width: <?php echo e(min($progress, 100)); ?>%"></div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                <?php echo e(number_format($workOrder->sent_pieces)); ?> / <?php echo e(number_format($workOrder->original_quantity)); ?>

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
                                <dd class="text-sm text-gray-900 dark:text-white"><?php echo e($workOrder->created_at->format('d/m/Y H:i')); ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Actualizado</dt>
                                <dd class="text-sm text-gray-900 dark:text-white"><?php echo e($workOrder->updated_at->format('d/m/Y H:i')); ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Signatures -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workOrder->purchaseOrder?->pdf_path): ?>
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Firmas del Documento</h2>
                    <button wire:click="openSignatureModal"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        Firmar Documento
                    </button>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($signatures->count() > 0): ?>
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $signatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $signature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex-shrink-0">
                                    <img src="<?php echo e($signature->signature_url); ?>" alt="Firma de <?php echo e($signature->user->name); ?>" 
                                        class="h-20 w-32 object-contain border border-gray-300 dark:border-gray-600 rounded bg-white">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo e($signature->user->name); ?>

                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo e($signature->signed_at->format('d/m/Y H:i')); ?>

                                    </p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($signature->ip_address): ?>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            IP: <?php echo e($signature->ip_address); ?>

                                        </p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($signature->signed_pdf_path): ?>
                                        <a href="<?php echo e(Storage::url($signature->signed_pdf_path)); ?>" target="_blank"
                                            class="mt-2 inline-flex items-center text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Descargar PDF Firmado
                                        </a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Firmado
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Este documento aún no ha sido firmado
                        </p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Status Log -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Historial de Cambios de Estado</h2>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workOrder->statusLogs->count() > 0): ?>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $workOrder->statusLogs->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <div class="relative pb-8">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>
                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800" style="background-color: <?php echo e($log->toStatus->color ?? '#6B7280'); ?>;">
                                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->fromStatus): ?>
                                                            <span class="font-medium" style="color: <?php echo e($log->fromStatus->color); ?>;"><?php echo e($log->fromStatus->name); ?></span>
                                                            →
                                                        <?php else: ?>
                                                            Creado como
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        <span class="font-medium" style="color: <?php echo e($log->toStatus->color); ?>;"><?php echo e($log->toStatus->name); ?></span>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->user): ?>
                                                            por <span class="font-medium text-gray-900 dark:text-white"><?php echo e($log->user->name); ?></span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </p>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->comments): ?>
                                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300"><?php echo e($log->comments); ?></p>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                                <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                                    <?php echo e($log->created_at->format('d/m/Y H:i')); ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay cambios de estado registrados.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Signature Modal Component -->
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('admin.signature-modal', ['@signatureCompleted' => 'refreshWorkOrder']);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-660525598-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views/livewire/admin/work-orders/wo-show.blade.php ENDPATH**/ ?>