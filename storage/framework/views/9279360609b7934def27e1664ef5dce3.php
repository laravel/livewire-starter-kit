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
                <?php echo e(__('Lista Preliminar')); ?> #<?php echo e($sentList->id); ?>

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
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center space-x-4">
                            <span class="px-4 py-2 text-sm font-semibold rounded-full 
                                <?php echo e($sentList->status === 'confirmed' ? 'bg-green-100 text-green-800' : ''); ?>

                                <?php echo e($sentList->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ''); ?>

                                <?php echo e($sentList->status === 'canceled' ? 'bg-red-100 text-red-800' : ''); ?>">
                                Estado: <?php echo e($sentList->status_label); ?>

                            </span>
                            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                Departamento: <?php echo e($sentList->department_label); ?>

                            </span>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Creado: <?php echo e($sentList->created_at->format('d/m/Y H:i')); ?>

                        </span>
                    </div>

                    
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 rounded-lg p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Período de Planificación</p>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                            Semana <?php echo e($sentList->start_date->weekOfYear); ?> - <?php echo e($sentList->start_date->year); ?>

                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            <?php echo e($sentList->start_date->format('d/m/Y')); ?> - <?php echo e($sentList->end_date->format('d/m/Y')); ?>

                        </p>
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Recursos Asignados
                        </h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Personas</dt>
                                <dd class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                    <?php echo e($sentList->num_persons); ?>

                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Turnos Asignados</dt>
                                <dd class="mt-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sentList->shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-1 mb-1">
                                            <?php echo e($shift->name); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                
                <div class="md:col-span-2 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Resumen de Capacidad
                        </h3>
                        <div class="grid grid-cols-3 gap-4">
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
                        </div>

                        
                        <div class="mt-4">
                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                                <span>Utilización de Capacidad</span>
                                <span><?php echo e($sentList->capacity_utilization); ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-600">
                                <div class="h-3 rounded-full transition-all duration-500 
                                    <?php echo e($sentList->capacity_utilization < 80 ? 'bg-green-500' : ''); ?>

                                    <?php echo e($sentList->capacity_utilization >= 80 && $sentList->capacity_utilization < 100 ? 'bg-yellow-500' : ''); ?>

                                    <?php echo e($sentList->capacity_utilization >= 100 ? 'bg-red-500' : ''); ?>"
                                    style="width: <?php echo e(min($sentList->capacity_utilization, 100)); ?>%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('admin.sent-lists.sent-list-department-view', ['sentList' => $sentList]);

$key = 'dept-view-'.$sentList->id;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-138053893-0', 'dept-view-'.$sentList->id);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

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
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\resources\views/sent-lists/show.blade.php ENDPATH**/ ?>