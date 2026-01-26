<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200" wire:poll.<?php echo e($refreshInterval); ?>s>
    
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-20 shadow-md">
        <div class="px-4 py-4">
            <div class="flex flex-col gap-4">
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="<?php echo e(asset('flexcon.png')); ?>" alt="Flexcon" class="h-10 sm:h-12 w-auto">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">LISTA DE ENVÍO</h1>
                    </div>
                    
                    <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4 animate-spin" wire:loading xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="hidden sm:inline">Auto-actualización cada <?php echo e($refreshInterval); ?>s</span>
                        <span class="sm:hidden"><?php echo e($refreshInterval); ?>s</span>
                    </div>
                </div>
                
                
                <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                    <div class="flex flex-col sm:flex-row gap-2 sm:items-center flex-1">
                        <label class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 sm:hidden">Filtros</label>
                        <select wire:model.live="filterDepartment" class="w-full sm:w-auto text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500">
                            <option value="">Todos los Departamentos</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Models\SentList::getDepartments(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>

                        <select wire:model.live="filterStatus" class="w-full sm:w-auto text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-blue-500">
                            <option value="">Todos los Estados</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Models\SentList::getStatuses(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>

                        <button 
                            wire:click="toggleCompleted" 
                            class="w-full sm:w-auto px-4 py-2 text-sm font-medium rounded-lg transition-colors <?php echo e($showCompleted ? 'bg-green-500 dark:bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'); ?>"
                        >
                            <?php echo e($showCompleted ? 'Ocultar' : 'Mostrar'); ?> Completados
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="px-4 sm:px-6 py-6 space-y-6 pb-20">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $workOrdersGrouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $workstationType => $workOrders): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                
                <div class="px-4 sm:px-6 py-4 <?php echo e($this->getWorkstationHeaderColor($workstationType)); ?>">
                    <h2 class="text-lg sm:text-xl font-bold text-white"><?php echo e($workstationType); ?></h2>
                </div>

                
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">DOC</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">WO #</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Item #</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Descripción</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Cantidad WO</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Piezas Enviadas</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Cantidad Pendiente</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Cantidad a Enviar</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Fecha Prog. A</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Fecha de Envío</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Fecha de Apertura</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">EG</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">PR</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $workOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $po = $wo->purchaseOrder;
                                    $part = $po->part;
                                    $completedLots = $wo->lots->where('status', \App\Models\Lot::STATUS_COMPLETED);
                                    $totalSent = $completedLots->sum('quantity');
                                    $pending = $wo->quantity - $wo->sent_pieces;
                                    $toSend = $completedLots->sum('quantity');
                                ?>

                                
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">WO</td>
                                    <td class="px-4 py-3 font-medium text-blue-600 dark:text-blue-400"><?php echo e($wo->wo_number); ?></td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300"><?php echo e($part->item_number); ?></td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs truncate" title="<?php echo e($part->description); ?>"><?php echo e($part->description); ?></td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100"><?php echo e(number_format($wo->quantity)); ?></td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300"><?php echo e(number_format($wo->sent_pieces)); ?></td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300"><?php echo e(number_format($pending)); ?></td>
                                    <td class="px-4 py-3 text-right font-bold bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200"><?php echo e(number_format($toSend)); ?></td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?php echo e($wo->scheduled_date?->format('m/d/Y') ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?php echo e($wo->ship_date?->format('m/d/Y') ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?php echo e($wo->created_at->format('m/d/Y')); ?></td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?php echo e($wo->sentList?->id ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?php echo e($wo->priority ?? '-'); ?></td>
                                </tr>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $completedLots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="bg-gray-50 dark:bg-gray-700/30 text-gray-600 dark:text-gray-400">
                                        <td class="px-4 py-2 pl-8 text-xs">Lote</td>
                                        <td class="px-4 py-2 text-xs"><?php echo e($wo->wo_number); ?>.<?php echo e($lot->lot_number); ?></td>
                                        <td class="px-4 py-2 text-xs"><?php echo e($part->item_number); ?></td>
                                        <td class="px-4 py-2 text-xs max-w-xs truncate" title="<?php echo e($lot->description ?? $part->description); ?>"><?php echo e($lot->description ?? $part->description); ?></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs font-medium"><?php echo e(number_format($lot->quantity)); ?></td>
                                        <td class="px-4 py-2 text-center text-xs"><?php echo e($wo->scheduled_date?->format('m/d/Y') ?? '-'); ?></td>
                                        <td class="px-4 py-2 text-center text-xs"><?php echo e($wo->ship_date?->format('m/d/Y') ?? '-'); ?></td>
                                        <td class="px-4 py-2 text-center text-xs"><?php echo e($wo->created_at->format('m/d/Y')); ?></td>
                                        <td class="px-4 py-2 text-center text-xs"><?php echo e($wo->sentList?->id ?? '-'); ?></td>
                                        <td class="px-4 py-2 text-center text-xs">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200">
                                                Completado
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($completedLots->count() > 1): ?>
                                    <tr class="bg-blue-50 dark:bg-blue-900/30 font-semibold">
                                        <td colspan="7" class="px-4 py-2 text-right text-gray-900 dark:text-gray-100">Total:</td>
                                        <td class="px-4 py-2 text-right text-gray-900 dark:text-gray-100"><?php echo e(number_format($totalSent)); ?></td>
                                        <td colspan="5"></td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="lg:hidden divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $workOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $po = $wo->purchaseOrder;
                            $part = $po->part;
                            $completedLots = $wo->lots->where('status', \App\Models\Lot::STATUS_COMPLETED);
                            $totalSent = $completedLots->sum('quantity');
                            $pending = $wo->quantity - $wo->sent_pieces;
                            $toSend = $completedLots->sum('quantity');
                        ?>

                        
                        <div class="p-4 space-y-3 bg-white dark:bg-gray-800">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">WO</span>
                                        <span class="text-base font-bold text-blue-600 dark:text-blue-400"><?php echo e($wo->wo_number); ?></span>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1"><?php echo e($part->item_number); ?></div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2"><?php echo e($part->description); ?></div>
                                </div>
                            </div>

                            
                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cantidad WO</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e(number_format($wo->quantity)); ?></div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Piezas Enviadas</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e(number_format($wo->sent_pieces)); ?></div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cantidad Pendiente</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e(number_format($pending)); ?></div>
                                </div>
                                <div class="bg-yellow-100 dark:bg-yellow-900/50 rounded-lg p-2">
                                    <div class="text-xs text-yellow-800 dark:text-yellow-200 mb-1 font-medium">Cantidad a Enviar</div>
                                    <div class="text-sm font-bold text-yellow-900 dark:text-yellow-100"><?php echo e(number_format($toSend)); ?></div>
                                </div>
                            </div>

                            
                            <div class="grid grid-cols-3 gap-2 text-xs pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Prog. A</div>
                                    <div class="text-gray-900 dark:text-gray-100"><?php echo e($wo->scheduled_date?->format('m/d/Y') ?? '-'); ?></div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Envío</div>
                                    <div class="text-gray-900 dark:text-gray-100"><?php echo e($wo->ship_date?->format('m/d/Y') ?? '-'); ?></div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Apertura</div>
                                    <div class="text-gray-900 dark:text-gray-100"><?php echo e($wo->created_at->format('m/d/Y')); ?></div>
                                </div>
                            </div>

                            
                            <div class="flex items-center gap-4 text-xs pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">EG:</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium ml-1"><?php echo e($wo->sentList?->id ?? '-'); ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">PR:</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium ml-1"><?php echo e($wo->priority ?? '-'); ?></span>
                                </div>
                            </div>
                        </div>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $completedLots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-4 pl-8 bg-gray-50 dark:bg-gray-700/30 space-y-2 border-l-4 border-green-500 dark:border-green-400">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lote</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo e($wo->wo_number); ?>.<?php echo e($lot->lot_number); ?></span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200">
                                        Completado
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2"><?php echo e($lot->description ?? $part->description); ?></div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Cantidad:</span>
                                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100"><?php echo e(number_format($lot->quantity)); ?></span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($completedLots->count() > 1): ?>
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/30">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Total:</span>
                                    <span class="text-base font-bold text-blue-900 dark:text-blue-100"><?php echo e(number_format($totalSent)); ?></span>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workOrdersGrouped->isEmpty()): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-gray-100">No hay lotes completados</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Los lotes completados aparecerán aquí automáticamente.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border-t border-gray-200 dark:border-gray-700 shadow-lg z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Última actualización: <span class="font-semibold text-gray-900 dark:text-gray-100"><?php echo e(now()->format('d/m/Y H:i:s')); ?></span></span>
                </div>
                <div class="flex items-center gap-4 sm:gap-6 text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-900 dark:text-gray-100"><?php echo e($workOrdersGrouped->flatten()->count()); ?></span>
                        <span class="text-xs">WOs</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-900 dark:text-gray-100"><?php echo e($workOrdersGrouped->flatten()->sum(fn($wo) => $wo->lots->count())); ?></span>
                        <span class="text-xs">Lotes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <?php
        $__scriptKey = '2282690654-0';
        ob_start();
    ?>
<script>
    // Auto-scroll suave cuando hay nuevos datos
    $wire.on('refresh-display', () => {
        console.log('Display refreshed');
    });

    // Notificación sonora cuando se completa un lote (opcional)
    $wire.on('lotCompleted', () => {
        // Puedes agregar un sonido de notificación aquí
        console.log('New lot completed!');
    });
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views/livewire/admin/sent-lists/shipping-list-display.blade.php ENDPATH**/ ?>