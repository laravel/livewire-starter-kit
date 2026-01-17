<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Estandar: Parte <?php echo e($standard->part->number); ?></h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Detalle completo del estandar
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="<?php echo e(route('admin.standards.index')); ?>"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200"
                        wire:navigate>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                    <a href="<?php echo e(route('admin.standards.edit', $standard)); ?>"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200"
                        wire:navigate>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                </div>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo e(session('error')); ?></span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info Card -->
            <div class="lg:col-span-2 space-y-6">
                <!-- General Information -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informacion General</h2>

                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parte</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
                                    <?php echo e($standard->part->number); ?>

                                    <span class="text-gray-500 dark:text-gray-400 font-normal">- <?php echo e($standard->part->description); ?></span>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                                <dd class="mt-1 flex flex-wrap gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($standard->active): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Activo
                                        </span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($is_current): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                Vigente
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Inactivo
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($standard->is_migrated): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            Sistema Nuevo
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Sistema Legacy
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </dd>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($standard->effective_date): ?>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Efectiva</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($standard->effective_date->format('d/m/Y')); ?></dd>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($configurationStats['total'] > 0): ?>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Resumen de Configuraciones</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        <?php echo e($configurationStats['total']); ?> configuracion(es)
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($configurationStats['min_productivity'] && $configurationStats['max_productivity']): ?>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                (<?php echo e($configurationStats['min_productivity']); ?> - <?php echo e($configurationStats['max_productivity']); ?> uph)
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </dd>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </dl>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($standard->description): ?>
                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripcion</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($standard->description); ?></dd>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- Metadata -->
                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Creado</dt>
                                    <dd class="mt-1 text-xs text-gray-600 dark:text-gray-400"><?php echo e($standard->created_at->format('d/m/Y H:i')); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ultima Actualizacion</dt>
                                    <dd class="mt-1 text-xs text-gray-600 dark:text-gray-400"><?php echo e($standard->updated_at->format('d/m/Y H:i')); ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Configurations Table -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($standard->configurations->count() > 0): ?>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Configuraciones de Produccion</h2>
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <?php echo e($standard->configurations->count()); ?> total
                                </span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Tipo de Estacion
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Estacion
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Personas
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                UPH
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Default
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Notas
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $standard->configurations->sortBy(['workstation_type', 'persons_required']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors <?php echo e($config->is_default ? 'bg-purple-50 dark:bg-purple-900/20' : ''); ?>">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <?php
                                                        $typeColor = match($config->workstation_type) {
                                                            'manual' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                            'semi_automatic' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                            'machine' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                                        };
                                                    ?>
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo e($typeColor); ?>">
                                                        <?php echo e($config->workstation_type_label); ?>

                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    <?php echo e($config->workstation_name); ?>

                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-semibold rounded-full">
                                                        <?php echo e($config->persons_required); ?>

                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                                    <span class="text-sm font-bold text-gray-900 dark:text-white">
                                                        <?php echo e(number_format($config->units_per_hour)); ?>

                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($config->is_default): ?>
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                            </svg>
                                                            Si
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-gray-400 dark:text-gray-500 text-sm">-</span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                    <?php echo e($config->notes ?? '-'); ?>

                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Legacy Information (if no configurations) -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$standard->is_migrated): ?>
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="p-6">
                                <div class="flex items-center mb-4">
                                    <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Configuracion Legacy</h2>
                                </div>

                                <p class="text-sm text-yellow-600 dark:text-yellow-400 mb-4">
                                    Este estandar usa el sistema antiguo. Considere migrarlo al nuevo sistema de configuraciones multiples.
                                </p>

                                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Unidades por Hora</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
                                            <?php echo e($standard->units_per_hour ?? 'N/A'); ?> uph
                                        </dd>
                                    </div>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($standard->workTable): ?>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mesa de Trabajo</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold"><?php echo e($standard->workTable->number); ?></dd>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($standard->semiAutoWorkTable): ?>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mesa Semi-Automatica</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold"><?php echo e($standard->semiAutoWorkTable->number); ?></dd>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($standard->machine): ?>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Maquina</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold"><?php echo e($standard->machine->name); ?></dd>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Personas 1</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
                                            <?php echo e($standard->persons_1 ?? 'N/A'); ?>

                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Personas 2</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
                                            <?php echo e($standard->persons_2 ?? 'N/A'); ?>

                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Personas 3</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">
                                            <?php echo e($standard->persons_3 ?? 'N/A'); ?>

                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Actions Card -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acciones</h2>

                        <div class="space-y-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($standard->active): ?>
                                <button wire:click="toggleActive" type="button"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Desactivar Estandar
                                </button>
                            <?php else: ?>
                                <button wire:click="toggleActive" type="button"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Activar Estandar
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <button wire:click="delete" wire:confirm="Esta seguro de que desea eliminar este estandar? Esta accion no se puede deshacer." type="button"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Eliminar Estandar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Configuration Stats -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($configurationStats['total'] > 0): ?>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Estadisticas</h2>

                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Total Configuraciones</dt>
                                    <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($configurationStats['total']); ?></dd>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($configurationStats['by_type'])): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $configurationStats['by_type']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo e($this->getWorkstationTypeLabel($type)); ?>

                                            </dt>
                                            <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($count); ?></dd>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($configurationStats['min_productivity']): ?>
                                    <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Productividad Min</dt>
                                        <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($configurationStats['min_productivity']); ?> uph</dd>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($configurationStats['max_productivity']): ?>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Productividad Max</dt>
                                        <dd class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($configurationStats['max_productivity']); ?> uph</dd>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Tiene Default</dt>
                                    <dd class="text-sm font-semibold">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($configurationStats['has_default']): ?>
                                            <span class="text-green-600 dark:text-green-400">Si</span>
                                        <?php else: ?>
                                            <span class="text-red-600 dark:text-red-400">No</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views/livewire/admin/standards/standard-show.blade.php ENDPATH**/ ?>