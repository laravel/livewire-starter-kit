<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                <?php echo e(__('Detalle Lista de Envío')); ?> #<?php echo e($sentList->id); ?>

            </h2>
            <a href="<?php echo e(route('admin.sent-lists.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <span class="px-4 py-2 text-sm font-semibold rounded-full 
                            <?php echo e($sentList->status === 'confirmed' ? 'bg-green-100 text-green-800' : ''); ?>

                            <?php echo e($sentList->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ''); ?>

                            <?php echo e($sentList->status === 'canceled' ? 'bg-red-100 text-red-800' : ''); ?>">
                            Estado: <?php echo e($sentList->status_label); ?>

                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Creado: <?php echo e($sentList->created_at->format('d/m/Y H:i')); ?>

                        </span>
                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentList->isPending()): ?>
                        <div class="flex space-x-2">
                            <form action="<?php echo e(route('admin.sent-lists.update', $sentList)); ?>" method="POST" class="inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500"
                                    onclick="return confirm('¿Confirmar esta lista de envío?');">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Confirmar
                                </button>
                            </form>
                            <form action="<?php echo e(route('admin.sent-lists.update', $sentList)); ?>" method="POST" class="inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="status" value="canceled">
                                <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500"
                                    onclick="return confirm('¿Cancelar esta lista de envío?');">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Cancelar
                                </button>
                            </form>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Información de la Orden de Compra
                        </h3>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de PO</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">
                                    <?php echo e($sentList->purchaseOrder->po_number ?? 'N/A'); ?>

                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parte</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <?php echo e($sentList->purchaseOrder->part->number ?? 'N/A'); ?>

                                </dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <?php echo e($sentList->purchaseOrder->part->description ?? 'N/A'); ?>

                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Información de Planificación
                        </h3>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Inicio</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <?php echo e($sentList->start_date->format('d/m/Y')); ?>

                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Fin</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <?php echo e($sentList->end_date->format('d/m/Y')); ?>

                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Personas</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">
                                    <?php echo e($sentList->num_persons); ?>

                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Turnos</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sentList->shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                            <?php echo e($shift->name); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Resumen de Capacidad
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Horas Disponibles</p>
                            <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                                <?php echo e(number_format($sentList->total_available_hours, 2)); ?>

                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Horas Usadas</p>
                            <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">
                                <?php echo e(number_format($sentList->used_hours, 2)); ?>

                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Horas Restantes</p>
                            <p class="text-3xl font-bold <?php echo e($sentList->remaining_hours > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'); ?>">
                                <?php echo e(number_format($sentList->remaining_hours, 2)); ?>

                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Utilización</p>
                            <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                                <?php echo e($sentList->capacity_utilization); ?>%
                            </p>
                        </div>
                    </div>

                    
                    <div class="mt-6">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                            <span>Utilización de Capacidad</span>
                            <span><?php echo e($sentList->capacity_utilization); ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-600">
                            <div class="h-4 rounded-full transition-all duration-500 
                                <?php echo e($sentList->capacity_utilization < 80 ? 'bg-green-500' : ''); ?>

                                <?php echo e($sentList->capacity_utilization >= 80 && $sentList->capacity_utilization < 100 ? 'bg-yellow-500' : ''); ?>

                                <?php echo e($sentList->capacity_utilization >= 100 ? 'bg-red-500' : ''); ?>"
                                style="width: <?php echo e(min($sentList->capacity_utilization, 100)); ?>%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Órdenes de Trabajo (<?php echo e($sentList->workOrders->count()); ?>)
                    </h3>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentList->workOrders->count() > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">WO #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Parte</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Modo Ensamble</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Horas Req.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sentList->workOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                                <?php echo e($wo->wo_number); ?>

                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                <?php echo e($wo->purchaseOrder->part->number ?? 'N/A'); ?>

                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                <?php echo e(number_format($wo->purchaseOrder->quantity ?? 0)); ?>

                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    <?php echo e(str_replace('_', ' ', ucfirst($wo->assembly_mode ?? 'N/A'))); ?>

                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                                <?php echo e(number_format($wo->required_hours ?? 0, 2)); ?> hrs
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                    style="background-color: <?php echo e($wo->status->color ?? '#6B7280'); ?>20; color: <?php echo e($wo->status->color ?? '#6B7280'); ?>">
                                                    <?php echo e($wo->status->name ?? 'N/A'); ?>

                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="<?php echo e(route('admin.work-orders.show', $wo)); ?>"
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    Ver Detalle
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No hay órdenes de trabajo asociadas.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3)): ?>
<?php $attributes = $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3; ?>
<?php unset($__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc8c9fd5d7827a77a31381de67195f0c3)): ?>
<?php $component = $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3; ?>
<?php unset($__componentOriginalc8c9fd5d7827a77a31381de67195f0c3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views\sent-lists\show.blade.php ENDPATH**/ ?>