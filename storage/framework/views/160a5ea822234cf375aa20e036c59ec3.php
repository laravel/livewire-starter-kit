<div>
    
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Flujo de Departamentos</h3>
        
        <div class="flex items-center justify-between">
            <?php
                $departments = [
                    \App\Models\SentList::DEPT_MATERIALS => ['label' => 'Materiales', 'icon' => 'cube'],
                    \App\Models\SentList::DEPT_PRODUCTION => ['label' => 'Producción', 'icon' => 'cog'],
                    \App\Models\SentList::DEPT_QUALITY => ['label' => 'Calidad', 'icon' => 'check-circle'],
                    \App\Models\SentList::DEPT_SHIPPING => ['label' => 'Envíos', 'icon' => 'truck'],
                ];
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deptKey => $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center <?php echo e(!$loop->last ? 'flex-1' : ''); ?>">
                    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'flex flex-col items-center',
                        'w-32'
                    ]); ?>">
                        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'w-16 h-16 rounded-full flex items-center justify-center mb-2 border-2',
                            'bg-blue-500 border-blue-500 text-white' => $sentList->current_department === $deptKey,
                            'bg-green-500 border-green-500 text-white' => $this->isPastDepartment($deptKey),
                            'bg-gray-200 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500' => $this->isFutureDepartment($deptKey),
                        ]); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isPastDepartment($deptKey)): ?>
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            <?php else: ?>
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'text-sm font-medium text-center',
                            'text-blue-600 dark:text-blue-400' => $sentList->current_department === $deptKey,
                            'text-green-600 dark:text-green-400' => $this->isPastDepartment($deptKey),
                            'text-gray-500 dark:text-gray-400' => $this->isFutureDepartment($deptKey),
                        ]); ?>">
                            <?php echo e($dept['label']); ?>

                        </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isPastDepartment($deptKey)): ?>
                            <?php
                                $approvedAt = match($deptKey) {
                                    \App\Models\SentList::DEPT_MATERIALS => $sentList->materials_approved_at,
                                    \App\Models\SentList::DEPT_PRODUCTION => $sentList->production_approved_at,
                                    \App\Models\SentList::DEPT_QUALITY => $sentList->quality_approved_at,
                                    default => null,
                                };
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($approvedAt): ?>
                                <span class="text-xs text-gray-500 mt-1">
                                    <?php echo e($approvedAt->format('d/m H:i')); ?>

                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>
                        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'flex-1 h-1 mx-2',
                            'bg-green-500' => $this->isPastDepartment($deptKey),
                            'bg-gray-300 dark:bg-gray-600' => !$this->isPastDepartment($deptKey),
                        ]); ?>"></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Purchase Orders en la Lista (<?php echo e($sentList->purchaseOrders->count()); ?>)
            </h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canEdit): ?>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Puede editar
                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PO Number</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Parte</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cantidad</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Horas</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lote/Viajero</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sentList->purchaseOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 font-medium text-blue-600 dark:text-blue-400">
                                <?php echo e($po->po_number); ?>

                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">
                                <?php echo e($po->part->number); ?>

                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                <?php echo e(Str::limit($po->part->description, 40)); ?>

                            </td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canEdit): ?>
                                    <input 
                                        type="number" 
                                        wire:model.blur="quantities.<?php echo e($po->id); ?>"
                                        class="w-24 text-right rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                                    />
                                <?php else: ?>
                                    <?php echo e(number_format($po->pivot->quantity)); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                <?php echo e(number_format($po->pivot->required_hours, 2)); ?>

                            </td>
                            <td class="px-4 py-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canEdit): ?>
                                    <input 
                                        type="text" 
                                        wire:model.blur="lotNumbers.<?php echo e($po->id); ?>"
                                        placeholder="Opcional"
                                        class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                                    />
                                <?php else: ?>
                                    <?php echo e($po->pivot->lot_number ?? '-'); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No hay Purchase Orders en esta lista
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Notas</h3>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canEdit): ?>
            <textarea 
                wire:model="generalNotes"
                rows="4"
                class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Agregar notas generales..."
            ></textarea>
        <?php else: ?>
            <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                <?php echo e($sentList->notes ?? 'Sin notas'); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canEdit): ?>
        <div class="flex justify-end gap-3">
            <button 
                wire:click="saveChanges"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
            >
                Guardar Cambios
            </button>
            <button 
                wire:click="openApprovalModal"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition"
            >
                Aprobar y Enviar al Siguiente Departamento
            </button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showApprovalModal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeApprovalModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Aprobar y Enviar
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            ¿Está seguro de que desea aprobar esta lista y enviarla al siguiente departamento?
                        </p>
                        <textarea 
                            wire:model="approvalNotes"
                            rows="3"
                            placeholder="Notas de aprobación (opcional)"
                            class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        ></textarea>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button 
                            wire:click="approveAndMoveToNextDepartment"
                            class="w-full inline-flex justify-center rounded-lg px-4 py-2 bg-green-600 text-white hover:bg-green-700 sm:w-auto"
                        >
                            Confirmar Aprobación
                        </button>
                        <button 
                            wire:click="closeApprovalModal"
                            class="mt-3 w-full inline-flex justify-center rounded-lg px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
        $__scriptKey = '396870157-0';
        ob_start();
    ?>
    <script>
        $wire.on('success', (message) => {
            alert(message);
        });

        $wire.on('error', (message) => {
            alert(message);
        });
    </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views/livewire/admin/sent-lists/sent-list-department-view.blade.php ENDPATH**/ ?>