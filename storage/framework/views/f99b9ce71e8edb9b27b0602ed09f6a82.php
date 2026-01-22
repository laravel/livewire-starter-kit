<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Nueva Orden de Compra</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Ingrese la información de la nueva PO
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="<?php echo e(route('admin.purchase-orders.index')); ?>"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver a la lista
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Container -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <form wire:submit="savePO" class="space-y-6">
                    <!-- PO Number, WO and Part -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="po_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Número de PO <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="po_number" id="po_number" type="text" placeholder="Ej: PO-2025-001"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                required />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['po_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label for="wo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                WO
                            </label>
                            <input wire:model="wo" id="wo" type="text" placeholder="Ej: WO-2025-001"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['wo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label for="part_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Parte <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="part_id" id="part_id"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                required>
                                <option value="">Seleccione una parte</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $parts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($part->id); ?>"><?php echo e($part->number); ?> - <?php echo e(Str::limit($part->description, 40)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['part_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="po_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha de PO <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="po_date" id="po_date" type="date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                required />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['po_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha de Entrega <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="due_date" id="due_date" type="date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                required />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <!-- Quantity and Price -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Cantidad <span class="text-red-500">*</span>
                            </label>
                            <input wire:model.live.debounce.500ms="quantity" id="quantity" type="number" min="1"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                required />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Precio Unitario <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 dark:text-gray-400">$</span>
                                <input wire:model.live.debounce.500ms="unit_price" id="unit_price" type="number" step="0.0001" min="0"
                                    class="w-full pl-7 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    required />
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['unit_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <!-- Price Validation Feedback -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($price_message): ?>
                        <div class="p-4 rounded-lg <?php echo e($price_valid ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800'); ?>">
                            <div class="flex items-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($price_valid): ?>
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-5 h-5 text-orange-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span class="<?php echo e($price_valid ? 'text-green-700 dark:text-green-300' : 'text-orange-700 dark:text-orange-300'); ?>">
                                    <?php echo e($price_message); ?>

                                </span>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expected_price !== null): ?>
                                <p class="mt-1 text-sm <?php echo e($price_valid ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400'); ?>">
                                    Precio esperado para cantidad <?php echo e(number_format($quantity)); ?>: $<?php echo e(number_format($expected_price, 4)); ?>

                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- PDF Upload -->
                    <div>
                        <label for="pdf_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Archivo PDF de la PO <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model="pdf_file" id="pdf_file" type="file" accept=".pdf"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                            <div wire:loading wire:target="pdf_file" class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-700/50 rounded-lg">
                                <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Cargando...</span>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pdf_file): ?>
                            <div class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                <div class="flex items-center text-green-700 dark:text-green-300">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm">Archivo seleccionado: <?php echo e($pdf_file->getClientOriginalName()); ?></span>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pdf_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Máximo 10MB, solo archivos PDF</p>
                    </div>

                    <!-- Comments -->
                    <div>
                        <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Comentarios
                        </label>
                        <textarea wire:model="comments" id="comments" rows="3"
                            placeholder="Comentarios adicionales..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 resize-none"></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['comments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="<?php echo e(route('admin.purchase-orders.index')); ?>"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200">
                            Cancelar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\resources\views/livewire/admin/purchase-orders/po-create.blade.php ENDPATH**/ ?>