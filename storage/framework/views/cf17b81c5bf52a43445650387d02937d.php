<div class="min-h-screen bg-gray-50 dark:bg-gray-900" wire:poll.30s>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="fixed top-4 right-4 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white px-4 py-3 rounded shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium"><?php echo e(session('message')); ?></span>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(session()->has('error')): ?>
        <div class="fixed top-4 right-4 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white px-4 py-3 rounded shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium"><?php echo e(session('error')); ?></span>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-20">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white">LISTA DE ENVÍO</h1>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4" wire:loading class="animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span class="hidden sm:inline">Actualización cada <?php echo e($refreshInterval); ?>s</span>
                        <span class="sm:hidden"><?php echo e($refreshInterval); ?>s</span>
                    </div>
                </div>
                
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <select wire:model.live="filterDepartment" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los Departamentos</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Models\SentList::getDepartments(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>

                    <select wire:model.live="filterStatus" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los Estados</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Models\SentList::getStatuses(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>

                    <button 
                        wire:click="toggleCompleted" 
                        class="px-4 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                    >
                        <?php echo e($showCompleted ? 'Ocultar' : 'Mostrar'); ?> Completados
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 pb-20">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $workOrdersGrouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $workstationType => $workOrders): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                
                <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-blue-800 dark:bg-gray-900/50">
                    <h2 class="text-white font-semibold text-gray-900 "><?php echo e($workstationType); ?></h2>
                </div>

                
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">DOC</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">WO #</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Item #</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider"># Parte</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Cant. WO</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Pz Enviadas</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Cant. Pendiente</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Cant. a Enviar</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha Prog. A</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha de Envío</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha de Apertura</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Materiales</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Calidad</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Producción</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">EG</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">PR</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $workOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $po = $wo->purchaseOrder;
                                    $part = $po->part;
                                    $allLots = $wo->lots;
                                    $completedLots = $wo->lots->where('status', \App\Models\Lot::STATUS_COMPLETED);
                                    $totalSent = $completedLots->sum('quantity');
                                    $cantWO = $wo->original_quantity; // Cantidad total del WO
                                    $pzEnviadas = $wo->sent_pieces; // Piezas enviadas
                                    $cantAEnviar = $cantWO - $pzEnviadas; // Cant. a Enviar = Cant. WO - Pz Enviadas
                                    $toSend = $cantAEnviar;
                                    
                                    // Obtener estados de departamentos (simulado por ahora)
                                    $departmentStatuses = [
                                        'materials' => 'pending',
                                        'quality' => 'pending',
                                        'production' => 'pending',
                                    ];
                                ?>

                                
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">WO</td>
                                    <td class="px-4 py-3 text-blue-600 dark:text-blue-400 font-medium"><?php echo e($po->wo); ?></td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300"><?php echo e($part->item_number); ?></td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white font-semibold"><?php echo e($part->number); ?></td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs truncate" title="<?php echo e($part->description); ?>"><?php echo e($part->description); ?></td>
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-medium"><?php echo e(number_format($cantWO)); ?></td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300"><?php echo e(number_format($pzEnviadas)); ?></td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300"><?php echo e(number_format($cantAEnviar)); ?></td>
                                    <td class="px-4 py-3 text-right font-semibold bg-yellow-50 dark:bg-yellow-900/20 text-yellow-900 dark:text-yellow-200"><?php echo e(number_format($toSend)); ?></td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?php echo e($wo->scheduled_send_date?->format('m/d/Y') ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?php echo e($wo->actual_send_date?->format('m/d/Y') ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?php echo e($wo->created_at->format('m/d/Y')); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <?php
                                            $materialsColor = match($departmentStatuses['materials']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        ?>
                                        <button wire:click="openDepartmentStatusModal(<?php echo e($wo->id); ?>, 'materials')" class="w-6 h-6 rounded <?php echo e($materialsColor); ?> hover:opacity-80 transition-opacity" title="Materiales"></button>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php
                                            $qualityColor = match($departmentStatuses['quality']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        ?>
                                        <button wire:click="openDepartmentStatusModal(<?php echo e($wo->id); ?>, 'quality')" class="w-6 h-6 rounded <?php echo e($qualityColor); ?> hover:opacity-80 transition-opacity" title="Calidad"></button>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php
                                            $productionColor = match($departmentStatuses['production']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        ?>
                                        <button wire:click="openDepartmentStatusModal(<?php echo e($wo->id); ?>, 'production')" class="w-6 h-6 rounded <?php echo e($productionColor); ?> hover:opacity-80 transition-opacity" title="Producción"></button>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?php echo e($wo->sentList?->id ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300"><?php echo e($wo->priority ?? '-'); ?></td>
                                </tr>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allLots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="bg-gray-50 dark:bg-gray-700/20">
                                        <td class="px-4 py-2 pl-8 text-xs text-gray-600 dark:text-gray-400">Lote</td>
                                        <td class="px-4 py-2 text-xs">
                                            <button wire:click="openLotModal(<?php echo e($wo->id); ?>)" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                                <?php echo e($lot->lot_number); ?>

                                            </button>
                                        </td>
                                        <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300"><?php echo e($part->item_number); ?></td>
                                        <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300 font-medium"><?php echo e($part->number); ?></td>
                                        <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300 max-w-xs truncate" title="<?php echo e($lot->description ?? $part->description); ?>"><?php echo e($lot->description ?? $part->description); ?></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs"></td>
                                        <td class="px-4 py-2 text-right text-xs font-medium text-gray-900 dark:text-white"><?php echo e(number_format($lot->quantity)); ?></td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400"><?php echo e($wo->scheduled_send_date?->format('m/d/Y') ?? '-'); ?></td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400"><?php echo e($wo->actual_send_date?->format('m/d/Y') ?? '-'); ?></td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400"><?php echo e($wo->created_at->format('m/d/Y')); ?></td>
                                        <td class="px-4 py-2 text-center text-xs"></td>
                                        <td class="px-4 py-2 text-center text-xs"></td>
                                        <td class="px-4 py-2 text-center text-xs"></td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-600 dark:text-gray-400"><?php echo e($wo->sentList?->id ?? '-'); ?></td>
                                        <td class="px-4 py-2 text-center text-xs">
                                            <?php
                                                $statusInfo = match($lot->status) {
                                                    \App\Models\Lot::STATUS_PENDING => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => 'Pendiente'],
                                                    \App\Models\Lot::STATUS_IN_PROGRESS => ['bg' => 'bg-blue-50 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300', 'label' => 'En Proceso'],
                                                    \App\Models\Lot::STATUS_COMPLETED => ['bg' => 'bg-green-50 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-300', 'label' => 'Completado'],
                                                    default => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => $lot->status],
                                                };
                                            ?>
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded <?php echo e($statusInfo['bg']); ?> <?php echo e($statusInfo['text']); ?>">
                                                <?php echo e($statusInfo['label']); ?>

                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allLots->count() > 1): ?>
                                    <tr class="bg-gray-100 dark:bg-gray-700/40 font-semibold">
                                        <td colspan="8" class="px-4 py-2 text-right text-gray-900 dark:text-white">Total:</td>
                                        <td class="px-4 py-2 text-right text-gray-900 dark:text-white"><?php echo e(number_format($allLots->sum('quantity'))); ?></td>
                                        <td colspan="8"></td>
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
                            $allLots = $wo->lots;
                            $completedLots = $wo->lots->where('status', \App\Models\Lot::STATUS_COMPLETED);
                            $cantWO = $wo->original_quantity; // Cantidad total del WO
                            $pzEnviadas = $wo->sent_pieces; // Piezas enviadas
                            $cantAEnviar = $cantWO - $pzEnviadas; // Cant. a Enviar = Cant. WO - Pz Enviadas
                            $toSend = $cantAEnviar;
                            
                            $departmentStatuses = [
                                'materials' => 'pending',
                                'quality' => 'pending',
                                'production' => 'pending',
                            ];
                        ?>

                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">WO</span>
                                        <span class="text-base font-semibold text-blue-600 dark:text-blue-400"><?php echo e($po->wo); ?></span>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white mb-1"><?php echo e($part->item_number); ?></div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2"><?php echo e($part->description); ?></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cant. WO</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(number_format($cantWO)); ?></div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pz Enviadas</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(number_format($pzEnviadas)); ?></div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cant. Pendiente</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(number_format($cantAEnviar)); ?></div>
                                </div>
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-2">
                                    <div class="text-xs text-yellow-700 dark:text-yellow-300 mb-1 font-medium">Cant. a Enviar</div>
                                    <div class="text-sm font-semibold text-yellow-900 dark:text-yellow-200"><?php echo e(number_format($toSend)); ?></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-xs pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Prog. A</div>
                                    <div class="text-gray-900 dark:text-white"><?php echo e($wo->scheduled_send_date?->format('m/d/Y') ?? '-'); ?></div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Envío</div>
                                    <div class="text-gray-900 dark:text-white"><?php echo e($wo->actual_send_date?->format('m/d/Y') ?? '-'); ?></div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Fecha Apertura</div>
                                    <div class="text-gray-900 dark:text-white"><?php echo e($wo->created_at->format('m/d/Y')); ?></div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 text-xs pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">EG:</span>
                                    <span class="text-gray-900 dark:text-white font-medium ml-1"><?php echo e($wo->sentList?->id ?? '-'); ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">PR:</span>
                                    <span class="text-gray-900 dark:text-white font-medium ml-1"><?php echo e($wo->priority ?? '-'); ?></span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Estado por Departamento</div>
                                <div class="flex items-center gap-4">
                                    <div class="flex flex-col items-center gap-1">
                                        <?php
                                            $materialsColor = match($departmentStatuses['materials']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        ?>
                                        <button wire:click="openDepartmentStatusModal(<?php echo e($wo->id); ?>, 'materials')" class="w-8 h-8 rounded <?php echo e($materialsColor); ?> hover:opacity-80 transition-opacity" title="Materiales"></button>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Mat.</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <?php
                                            $qualityColor = match($departmentStatuses['quality']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        ?>
                                        <button wire:click="openDepartmentStatusModal(<?php echo e($wo->id); ?>, 'quality')" class="w-8 h-8 rounded <?php echo e($qualityColor); ?> hover:opacity-80 transition-opacity" title="Calidad"></button>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Cal.</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <?php
                                            $productionColor = match($departmentStatuses['production']) {
                                                'rejected' => 'bg-red-500',
                                                'pending' => 'bg-yellow-400',
                                                'in_progress' => 'bg-blue-500',
                                                'approved' => 'bg-green-500',
                                                default => 'bg-gray-400',
                                            };
                                        ?>
                                        <button wire:click="openDepartmentStatusModal(<?php echo e($wo->id); ?>, 'production')" class="w-8 h-8 rounded <?php echo e($productionColor); ?> hover:opacity-80 transition-opacity" title="Producción"></button>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Prod.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allLots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-4 pl-8 bg-gray-50 dark:bg-gray-700/20 space-y-2 border-l-2 border-gray-300 dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <button wire:click="openLotModal(<?php echo e($wo->id); ?>)" class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lote</span>
                                        <span><?php echo e($lot->lot_number); ?></span>
                                    </button>
                                    <?php
                                        $statusInfo = match($lot->status) {
                                            \App\Models\Lot::STATUS_PENDING => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => 'Pendiente'],
                                            \App\Models\Lot::STATUS_IN_PROGRESS => ['bg' => 'bg-blue-50 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300', 'label' => 'En Proceso'],
                                            \App\Models\Lot::STATUS_COMPLETED => ['bg' => 'bg-green-50 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-300', 'label' => 'Completado'],
                                            default => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => $lot->status],
                                        };
                                    ?>
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded <?php echo e($statusInfo['bg']); ?> <?php echo e($statusInfo['text']); ?>">
                                        <?php echo e($statusInfo['label']); ?>

                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2"><?php echo e($lot->description ?? $part->description); ?></div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Cantidad:</span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(number_format($lot->quantity)); ?></span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allLots->count() > 1): ?>
                            <div class="p-4 bg-gray-100 dark:bg-gray-700/40">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Total:</span>
                                    <span class="text-base font-semibold text-gray-900 dark:text-white"><?php echo e(number_format($allLots->sum('quantity'))); ?></span>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workOrdersGrouped->isEmpty()): ?>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-base font-medium text-gray-900 dark:text-white">No hay lotes</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Los lotes aparecerán aquí automáticamente.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Última actualización: <span class="font-medium text-gray-900 dark:text-white"><?php echo e(now()->format('d/m/Y H:i:s')); ?></span></span>
                </div>
                <div class="flex items-center gap-4 sm:gap-6 text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo e($workOrdersGrouped->flatten()->count()); ?></span>
                        <span>WOs</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo e($workOrdersGrouped->flatten()->sum(fn($wo) => $wo->lots->count())); ?></span>
                        <span>Lotes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLotModal && $selectedWorkOrder): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeLotModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Gestión de Lotes</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    WO: <?php echo e($selectedWorkOrder->purchaseOrder->wo); ?> | Parte: <?php echo e($selectedWorkOrder->purchaseOrder->part->number); ?>

                                </p>
                            </div>
                            <button wire:click="closeLotModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($lots) > 0): ?>
                            <div class="space-y-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $lots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $lot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-700">
                                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    No. Lote/Viajero
                                                </label>
                                                <input 
                                                    type="text" 
                                                    wire:model="lots.<?php echo e($index); ?>.number"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="Ej: 001"
                                                >
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["lots.{$index}.number"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block"><?php echo e($message); ?></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Cantidad
                                                </label>
                                                <input 
                                                    type="number" 
                                                    wire:model="lots.<?php echo e($index); ?>.quantity"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="Ej: 100"
                                                    min="1"
                                                >
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["lots.{$index}.quantity"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1 block"><?php echo e($message); ?></span>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                        <button 
                                            wire:click="removeLot(<?php echo e($index); ?>)"
                                            class="flex-shrink-0 p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                            title="Eliminar lote"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-sm">No hay lotes. Agrega uno nuevo.</p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="mt-4">
                            <button 
                                wire:click="addLot"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Agregar Lote
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button 
                            wire:click="closeLotModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            wire:click="saveLots"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors"
                        >
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDepartmentStatusModal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 transition-opacity" wire:click="closeDepartmentStatusModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Estado de Departamentos</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Actualizar estado por departamento</p>
                            </div>
                            <button wire:click="closeDepartmentStatusModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['materials' => 'Materiales', 'quality' => 'Calidad', 'production' => 'Producción']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deptKey => $deptLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <?php echo e($deptLabel); ?>

                                </label>
                                <div class="grid grid-cols-4 gap-2">
                                    <button 
                                        wire:click="updateDepartmentStatus('<?php echo e($deptKey); ?>', 'rejected')" 
                                        class="px-3 py-2 text-xs font-medium border transition-colors <?php echo e($departmentStatuses[$deptKey] === 'rejected' ? 'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400'); ?>"
                                    >
                                        Rechazado
                                    </button>
                                    <button 
                                        wire:click="updateDepartmentStatus('<?php echo e($deptKey); ?>', 'pending')" 
                                        class="px-3 py-2 text-xs font-medium border transition-colors <?php echo e($departmentStatuses[$deptKey] === 'pending' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400'); ?>"
                                    >
                                        Pendiente
                                    </button>
                                    <button 
                                        wire:click="updateDepartmentStatus('<?php echo e($deptKey); ?>', 'in_progress')" 
                                        class="px-3 py-2 text-xs font-medium border transition-colors <?php echo e($departmentStatuses[$deptKey] === 'in_progress' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400'); ?>"
                                    >
                                        En Progreso
                                    </button>
                                    <button 
                                        wire:click="updateDepartmentStatus('<?php echo e($deptKey); ?>', 'approved')" 
                                        class="px-3 py-2 text-xs font-medium border transition-colors <?php echo e($departmentStatuses[$deptKey] === 'approved' ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-400'); ?>"
                                    >
                                        Aprobado
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button 
                            wire:click="closeDepartmentStatusModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            wire:click="saveDepartmentStatuses"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors"
                        >
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

    <?php
        $__scriptKey = '2282690654-0';
        ob_start();
    ?>
<script>
    $wire.on('refresh-display', () => {
        console.log('Display refreshed');
    });

    $wire.on('lotCompleted', () => {
        console.log('New lot completed!');
    });
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views/livewire/admin/sent-lists/shipping-list-display.blade.php ENDPATH**/ ?>