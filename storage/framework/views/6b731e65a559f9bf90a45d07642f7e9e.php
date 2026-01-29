
<div>
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Paso 2 de 3 - Cálculo de Horas Necesarias
    </h2>

    <div class="grid gap-6 lg:grid-cols-3">
        
        <div class="lg:col-span-1 space-y-4">
            <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4">
                <h3 class="font-medium text-gray-900 dark:text-white mb-4">Agregar Número de Parte</h3>
                
                
                <button 
                    wire:click="openPOModal" 
                    type="button"
                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Cargar desde POs
                </button>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($parts->isEmpty()): ?>
                    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-xs text-yellow-700 dark:text-yellow-300">
                            No hay partes con estándar activo. Debe crear un estándar primero.
                        </p>
                        <a href="<?php echo e(route('admin.standards.create')); ?>" class="text-xs text-blue-600 hover:underline mt-1 inline-block">
                            Crear Estándar →
                        </a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="lg:col-span-2 space-y-4">
            
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 text-center">
                    <p class="text-sm text-blue-700 dark:text-blue-300">Disponibles</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?php echo e(number_format($totalAvailableHours, 2)); ?></p>
                </div>
                <div class="rounded-lg bg-orange-50 dark:bg-orange-900/20 p-4 text-center">
                    <p class="text-sm text-orange-700 dark:text-orange-300">Requeridas</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400"><?php echo e(number_format($totalRequiredHours, 2)); ?></p>
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
                    ]); ?>">Diferencia</p>
                    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'text-2xl font-bold',
                        'text-green-600 dark:text-green-400' => $remainingHours >= 0,
                        'text-red-600 dark:text-red-400' => $remainingHours < 0,
                    ]); ?>"><?php echo e(number_format($remainingHours, 2)); ?></p>
                </div>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($suggestedOvertime > 0): ?>
                <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-yellow-800 dark:text-yellow-200">Sugerencia de Tiempo Extra</p>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                Se requieren <strong><?php echo e(number_format($suggestedOvertime, 2)); ?> horas</strong> adicionales.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"># Parte</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PO</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo Estación</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Personas</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Unid/Hora</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Horas Req.</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $workOrderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white"><?php echo e($item['part_number']); ?></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(Str::limit($item['part_description'] ?? '', 25)); ?></div>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    <?php echo e($item['po_number'] ?? '-'); ?>

                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    <?php echo e(number_format($item['quantity'])); ?>

                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    <?php echo e($item['configuration']['workstation_type_label'] ?? 'N/A'); ?>

                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    <?php echo e($item['configuration']['persons_required'] ?? 'N/A'); ?>

                                </td>
                                <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">
                                    <?php echo e(number_format($item['configuration']['units_per_hour'] ?? 0)); ?>

                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                    <?php echo e(number_format($item['required_hours'], 2)); ?>

                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button 
                                        wire:click="removeWorkOrderItem(<?php echo e($index); ?>)"
                                        type="button"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No hay números de parte agregados
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="flex justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button 
            wire:click="previousStep" 
            type="button"
            class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors duration-200"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Anterior
        </button>
        <button 
            wire:click="nextStep" 
            type="button"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
        >
            Siguiente
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>
</div>



<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPOModal): ?>
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closePOModal"></div>

        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Seleccionar Purchase Orders
                    </h3>
                    <button wire:click="closePOModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                
                <div class="mb-4">
                    <input 
                        wire:model.live.debounce.300ms="poSearchTerm"
                        type="text" 
                        placeholder="Buscar por PO o número de parte..."
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                
                <div class="max-h-96 overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->availablePOs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $standard = $po->part->standards->where('active', true)->first();
                            $configurations = $standard ? $standard->configurations : collect();
                            $isSelected = in_array($po->id, $selectedPOs);
                        ?>
                        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'border rounded-lg p-4 mb-3',
                            'border-blue-500 bg-blue-50 dark:bg-blue-900/20' => $isSelected,
                            'border-gray-200 dark:border-gray-700' => !$isSelected,
                        ]); ?>">
                            <div class="flex items-start gap-3">
                                
                                <input 
                                    type="checkbox" 
                                    wire:click="togglePOSelection(<?php echo e($po->id); ?>)"
                                    <?php if($isSelected): echo 'checked'; endif; ?>
                                    class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />

                                <div class="flex-1">
                                    
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($po->po_number); ?></span>
                                            <span class="mx-2 text-gray-400">|</span>
                                            <span class="text-gray-700 dark:text-gray-300"><?php echo e($po->part->number); ?></span>
                                        </div>
                                        <span class="text-sm text-gray-500">Qty: <?php echo e(number_format($po->quantity)); ?></span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3"><?php echo e($po->part->description); ?></p>

                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSelected && $configurations->isNotEmpty()): ?>
                                        <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Seleccionar Configuración:
                                            </label>
                                            <div class="space-y-2">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $configurations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $canUse = $config->persons_required <= $numPersons;
                                                    ?>
                                                    <label class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                        'flex items-center p-2 rounded cursor-pointer',
                                                        'hover:bg-gray-100 dark:hover:bg-gray-800' => $canUse,
                                                        'opacity-50 cursor-not-allowed' => !$canUse,
                                                    ]); ?>">
                                                        <input 
                                                            type="radio" 
                                                            name="config_<?php echo e($po->id); ?>"
                                                            wire:click="setConfigurationForPO(<?php echo e($po->id); ?>, <?php echo e($config->id); ?>)"
                                                            <?php if(!$canUse): echo 'disabled'; endif; ?>
                                                            <?php if(isset($poConfigurations[$po->id]) && $poConfigurations[$po->id] == $config->id): echo 'checked'; endif; ?>
                                                            class="mr-2"
                                                        />
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                                            <?php echo e($config->workstation_type_label); ?> - 
                                                            <?php echo e($config->persons_required); ?> persona(s) - 
                                                            <?php echo e($config->units_per_hour); ?> uph
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$canUse): ?>
                                                                <span class="text-red-500 ml-2">(Requiere más personas)</span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </span>
                                                    </label>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!isset($poConfigurations[$po->id])): ?>
                                                <p class="text-xs text-gray-500 mt-2">
                                                    Si no selecciona, se usará la configuración óptima automáticamente
                                                </p>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No hay POs aprobados con configuraciones disponibles
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                <button 
                    wire:click="addSelectedPOs"
                    type="button"
                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                >
                    Agregar Seleccionados (<?php echo e(count($selectedPOs)); ?>)
                </button>
                <button 
                    wire:click="closePOModal"
                    type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views/livewire/admin/capacity-wizard/step2.blade.php ENDPATH**/ ?>