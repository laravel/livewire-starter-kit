<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Crear Parte</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Ingrese la información de la nueva parte
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('admin.parts.index')): ?>
                        <a href="<?php echo e(route('admin.parts.index')); ?>"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver a la lista
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card Container -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <form wire:submit="savePart" class="space-y-6">
                    <!-- Números -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Número de Parte <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="number" id="number" type="text" placeholder="Ej: PART-001"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                required />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['number'];
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
                            <label for="item_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Número de Ítem <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="item_number" id="item_number" type="text" placeholder="Ej: ITEM-001"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                required />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['item_number'];
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

                    <!-- Unidad de Medida -->
                    <div>
                        <label for="unit_of_measure" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Unidad de Medida
                        </label>
                        <input wire:model="unit_of_measure" id="unit_of_measure" type="text" placeholder="Ej: PZA, KG, M"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['unit_of_measure'];
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

                    <!-- Descripción -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Descripción
                        </label>
                        <textarea wire:model="description" id="description" rows="3"
                            placeholder="Descripción detallada de la parte..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 resize-none"></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['description'];
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

                    <!-- Notas -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Notas
                        </label>
                        <input wire:model="notes" id="notes" type="text" placeholder="Notas adicionales..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['notes'];
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

                    <!-- Estado Activo -->
                    <div class="flex items-center">
                        <input wire:model="active" id="active" type="checkbox"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
                        <label for="active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Parte activa
                        </label>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="<?php echo e(route('admin.parts.index')); ?>"
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
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\resources\views/livewire/admin/parts/part-create.blade.php ENDPATH**/ ?>