
<div>
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Paso 2 de 3 - Cálculo de Horas Necesarias
    </h2>

    <div class="grid gap-6 lg:grid-cols-3">
        
        <div class="lg:col-span-1 space-y-4">
            <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4">
                <h3 class="font-medium text-gray-900 dark:text-white mb-4">Agregar Número de Parte</h3>
                
                
                <div class="mb-4" wire:ignore>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Número de Parte
                        <span class="text-xs text-gray-500">(<?php echo e($parts->count()); ?> disponibles)</span>
                    </label>
                    <select 
                        id="part-select"
                        placeholder="Buscar número de parte..."
                        autocomplete="off"
                    >
                        <option value="">Seleccionar parte...</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $parts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($part->id); ?>"><?php echo e($part->number); ?> - <?php echo e(Str::limit($part->description, 40)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['currentPartId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Cantidad del WO
                    </label>
                    <input 
                        wire:model="currentQuantity" 
                        type="number" 
                        min="1"
                        placeholder="0"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['currentQuantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <button 
                    wire:click="addWorkOrderItem" 
                    type="button"
                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Agregar
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
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
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
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    <?php echo e(number_format($item['quantity'])); ?>

                                </td>
                                <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">
                                    <?php echo e(number_format($item['units_per_hour'] ?? 0)); ?>

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
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
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


    <?php
        $__scriptKey = '1878975925-1';
        ob_start();
    ?>
<script>
    // Initialize Tom Select when component loads
    $wire.on('initTomSelect', () => {
        initPartSelect();
    });

    // Clear Tom Select when item is added successfully
    $wire.on('partAdded', () => {
        const selectEl = document.getElementById('part-select');
        if (selectEl && selectEl.tomselect) {
            selectEl.tomselect.clear();
        }
    });

    function initPartSelect() {
        const selectEl = document.getElementById('part-select');
        if (!selectEl) return;
        
        // Destroy existing instance if any
        if (selectEl.tomselect) {
            selectEl.tomselect.destroy();
        }

        new TomSelect('#part-select', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: 'Buscar número de parte...',
            allowEmptyOption: true,
            render: {
                option: function(data, escape) {
                    return '<div class="py-2 px-3">' + escape(data.text) + '</div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                },
                no_results: function(data, escape) {
                    return '<div class="no-results py-2 px-3 text-gray-500">No se encontraron resultados para "' + escape(data.input) + '"</div>';
                }
            },
            onChange: function(value) {
                // Sync with Livewire using $wire
                $wire.set('currentPartId', value ? parseInt(value) : null);
            }
        });
    }

    // Initialize on first load
    setTimeout(() => initPartSelect(), 100);
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?><?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views\livewire\admin\capacity-wizard\step2.blade.php ENDPATH**/ ?>