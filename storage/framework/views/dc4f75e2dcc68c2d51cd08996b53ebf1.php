
<div>
    <?php
        $weekNumber = \Carbon\Carbon::parse($startDate)->weekOfYear;
        $year = \Carbon\Carbon::parse($startDate)->year;
    ?>
    
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Paso 3 de 3 - Cierre - Período Semana <?php echo e($weekNumber); ?>-<?php echo e($year); ?>

    </h2>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($generatedSentListId): ?>
        
        <div class="text-center py-8">
            <div class="flex justify-center mb-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <?php if (isset($component)) { $__componentOriginal99e1287553cbf55f278732425b3f00bd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99e1287553cbf55f278732425b3f00bd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.check-circle','data' => ['class' => 'h-10 w-10 text-green-600 dark:text-green-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.check-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-10 w-10 text-green-600 dark:text-green-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99e1287553cbf55f278732425b3f00bd)): ?>
<?php $attributes = $__attributesOriginal99e1287553cbf55f278732425b3f00bd; ?>
<?php unset($__attributesOriginal99e1287553cbf55f278732425b3f00bd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99e1287553cbf55f278732425b3f00bd)): ?>
<?php $component = $__componentOriginal99e1287553cbf55f278732425b3f00bd; ?>
<?php unset($__componentOriginal99e1287553cbf55f278732425b3f00bd); ?>
<?php endif; ?>
                </div>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">¡Lista Generada Exitosamente!</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                La lista de planificación #<?php echo e($generatedSentListId); ?> ha sido creada para el departamento de Materiales.
            </p>
            <div class="flex justify-center gap-4">
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'viewSentList','variant' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'viewSentList','variant' => 'primary']); ?>
                    <?php if (isset($component)) { $__componentOriginal2e57535a42d25d5415c31aa83132341b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2e57535a42d25d5415c31aa83132341b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.eye','data' => ['class' => 'w-4 h-4 mr-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.eye'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 mr-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2e57535a42d25d5415c31aa83132341b)): ?>
<?php $attributes = $__attributesOriginal2e57535a42d25d5415c31aa83132341b; ?>
<?php unset($__attributesOriginal2e57535a42d25d5415c31aa83132341b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2e57535a42d25d5415c31aa83132341b)): ?>
<?php $component = $__componentOriginal2e57535a42d25d5415c31aa83132341b; ?>
<?php unset($__componentOriginal2e57535a42d25d5415c31aa83132341b); ?>
<?php endif; ?>
                    Ver Lista
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'resetWizard','variant' => 'ghost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'resetWizard','variant' => 'ghost']); ?>
                    <?php if (isset($component)) { $__componentOriginal18ce857dfc449fdd246010f7208cb6d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18ce857dfc449fdd246010f7208cb6d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.arrow-path','data' => ['class' => 'w-4 h-4 mr-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.arrow-path'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 mr-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18ce857dfc449fdd246010f7208cb6d5)): ?>
<?php $attributes = $__attributesOriginal18ce857dfc449fdd246010f7208cb6d5; ?>
<?php unset($__attributesOriginal18ce857dfc449fdd246010f7208cb6d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18ce857dfc449fdd246010f7208cb6d5)): ?>
<?php $component = $__componentOriginal18ce857dfc449fdd246010f7208cb6d5; ?>
<?php unset($__componentOriginal18ce857dfc449fdd246010f7208cb6d5); ?>
<?php endif; ?>
                    Nueva Calculación
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        
        <div class="space-y-6">
            
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Turnos Seleccionados</p>
                    <p class="font-medium text-gray-900 dark:text-white"><?php echo e(count($selectedShifts)); ?> turno(s)</p>
                </div>
                <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Personal</p>
                    <p class="font-medium text-gray-900 dark:text-white"><?php echo e($numPersons); ?> persona(s)</p>
                </div>
                <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Período</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        Semana <?php echo e(\Carbon\Carbon::parse($startDate)->weekOfYear); ?> - <?php echo e(\Carbon\Carbon::parse($startDate)->year); ?>

                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <?php echo e(\Carbon\Carbon::parse($startDate)->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::parse($endDate)->format('d/m/Y')); ?>

                    </p>
                </div>
            </div>

            
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 text-center">
                    <p class="text-sm text-blue-700 dark:text-blue-300">Horas Disponibles</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400"><?php echo e(number_format($totalAvailableHours, 2)); ?></p>
                </div>
                <div class="rounded-lg bg-orange-50 dark:bg-orange-900/20 p-4 text-center">
                    <p class="text-sm text-orange-700 dark:text-orange-300">Horas Utilizadas</p>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400"><?php echo e(number_format($totalRequiredHours, 2)); ?></p>
                </div>
                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'rounded-lg p-4 text-center',
                    'bg-green-50 dark:bg-green-900/20' => $remainingHours >= 0,
                    'bg-red-50 dark:bg-red-900/20' => $remainingHours < 0,
                ]); ?>">
                    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'text-sm',
                        'text-green-700 dark:text-green-300' => $remainingHours >= 0,
                        'text-red-700 dark:text-red-300' => $remainingHours < 0,
                    ]); ?>">Horas Restantes</p>
                    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'text-3xl font-bold',
                        'text-green-600 dark:text-green-400' => $remainingHours >= 0,
                        'text-red-600 dark:text-red-400' => $remainingHours < 0,
                    ]); ?>"><?php echo e(number_format($remainingHours, 2)); ?></p>
                </div>
            </div>

            
            <div>
                <h3 class="font-medium text-gray-900 dark:text-white mb-3">Resumen de Números de Parte</h3>
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Número de Parte</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Horas Req.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $workOrderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400"><?php echo e($index + 1); ?></td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white"><?php echo e($item['part_number']); ?></td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300"><?php echo e(Str::limit($item['part_description'] ?? '', 40)); ?></td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300"><?php echo e(number_format($item['quantity'])); ?></td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white"><?php echo e(number_format($item['required_hours'], 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Total:</td>
                                <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white"><?php echo e(number_format($totalRequiredHours, 2)); ?> hrs</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($suggestedOvertime > 0): ?>
                <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-4">
                    <div class="flex items-center gap-3">
                        <?php if (isset($component)) { $__componentOriginal7f0e8d69add49581695c1337b3f85fff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7f0e8d69add49581695c1337b3f85fff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.exclamation-triangle','data' => ['class' => 'w-6 h-6 text-yellow-600 dark:text-yellow-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.exclamation-triangle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6 text-yellow-600 dark:text-yellow-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7f0e8d69add49581695c1337b3f85fff)): ?>
<?php $attributes = $__attributesOriginal7f0e8d69add49581695c1337b3f85fff; ?>
<?php unset($__attributesOriginal7f0e8d69add49581695c1337b3f85fff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7f0e8d69add49581695c1337b3f85fff)): ?>
<?php $component = $__componentOriginal7f0e8d69add49581695c1337b3f85fff; ?>
<?php unset($__componentOriginal7f0e8d69add49581695c1337b3f85fff); ?>
<?php endif; ?>
                        <div>
                            <p class="font-medium text-yellow-800 dark:text-yellow-200">Atención: Capacidad Excedida</p>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                Se requieren <?php echo e(number_format($suggestedOvertime, 2)); ?> horas adicionales de tiempo extra.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="flex justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'previousStep','variant' => 'ghost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'previousStep','variant' => 'ghost']); ?>
                <?php if (isset($component)) { $__componentOriginale41799ef1b2ae0b0e320ff9b21dacd08 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale41799ef1b2ae0b0e320ff9b21dacd08 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.arrow-left','data' => ['class' => 'w-4 h-4 mr-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.arrow-left'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 mr-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale41799ef1b2ae0b0e320ff9b21dacd08)): ?>
<?php $attributes = $__attributesOriginale41799ef1b2ae0b0e320ff9b21dacd08; ?>
<?php unset($__attributesOriginale41799ef1b2ae0b0e320ff9b21dacd08); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale41799ef1b2ae0b0e320ff9b21dacd08)): ?>
<?php $component = $__componentOriginale41799ef1b2ae0b0e320ff9b21dacd08; ?>
<?php unset($__componentOriginale41799ef1b2ae0b0e320ff9b21dacd08); ?>
<?php endif; ?>
                Anterior
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
            <div class="flex gap-3">
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'resetWizard','variant' => 'ghost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'resetWizard','variant' => 'ghost']); ?>
                    <?php if (isset($component)) { $__componentOriginal18ce857dfc449fdd246010f7208cb6d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18ce857dfc449fdd246010f7208cb6d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.arrow-path','data' => ['class' => 'w-4 h-4 mr-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.arrow-path'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 mr-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18ce857dfc449fdd246010f7208cb6d5)): ?>
<?php $attributes = $__attributesOriginal18ce857dfc449fdd246010f7208cb6d5; ?>
<?php unset($__attributesOriginal18ce857dfc449fdd246010f7208cb6d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18ce857dfc449fdd246010f7208cb6d5)): ?>
<?php $component = $__componentOriginal18ce857dfc449fdd246010f7208cb6d5; ?>
<?php unset($__componentOriginal18ce857dfc449fdd246010f7208cb6d5); ?>
<?php endif; ?>
                    Nueva Calculación
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'generateSentList','variant' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'generateSentList','variant' => 'primary']); ?>
                    <?php if (isset($component)) { $__componentOriginal42dcb69862a510f1b92ffbdd4006e172 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42dcb69862a510f1b92ffbdd4006e172 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.paper-airplane','data' => ['class' => 'w-4 h-4 mr-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.paper-airplane'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4 mr-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42dcb69862a510f1b92ffbdd4006e172)): ?>
<?php $attributes = $__attributesOriginal42dcb69862a510f1b92ffbdd4006e172; ?>
<?php unset($__attributesOriginal42dcb69862a510f1b92ffbdd4006e172); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42dcb69862a510f1b92ffbdd4006e172)): ?>
<?php $component = $__componentOriginal42dcb69862a510f1b92ffbdd4006e172; ?>
<?php unset($__componentOriginal42dcb69862a510f1b92ffbdd4006e172); ?>
<?php endif; ?>
                    Generar Lista para Materiales
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\resources\views/livewire/admin/capacity-wizard/step3.blade.php ENDPATH**/ ?>