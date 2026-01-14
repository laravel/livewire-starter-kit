<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="<?php echo e(route('admin.production-statuses.index')); ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($productionStatus->name); ?></h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Detalles del estado de producción
                        </p>
                    </div>
                </div>
                <a href="<?php echo e(route('admin.production-statuses.edit', $productionStatus)); ?>"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
            </div>
        </div>

        <!-- Information Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Información General</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full" style="background-color: <?php echo e($productionStatus->color); ?>"></div>
                            <?php echo e($productionStatus->name); ?>

                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Color</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white flex items-center gap-2">
                            <div class="w-8 h-8 rounded border border-gray-300 dark:border-gray-600" style="background-color: <?php echo e($productionStatus->color); ?>"></div>
                            <?php echo e($productionStatus->color); ?>

                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Orden</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($productionStatus->order); ?></dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                        <dd class="mt-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($productionStatus->active): ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">
                                    Activo
                                </span>
                            <?php else: ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full dark:bg-red-900/30 dark:text-red-400">
                                    Inactivo
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            <?php echo e($productionStatus->description ?? 'Sin descripción'); ?>

                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Creación</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($productionStatus->created_at->format('d/m/Y H:i')); ?></dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Última Actualización</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($productionStatus->updated_at->format('d/m/Y H:i')); ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Usage Statistics -->
        <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Uso del Estado</h3>
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mesas con este estado</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white"><?php echo e($productionStatus->tables->count()); ?></dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Semi-automáticos con este estado</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white"><?php echo e($productionStatus->semiAutomatics->count()); ?></dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Máquinas con este estado</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white"><?php echo e($productionStatus->machines->count()); ?></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views\livewire\admin\production-statuses\production-status-show.blade.php ENDPATH**/ ?>