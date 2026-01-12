<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Panel de Empleado</h1>
        <p class="text-gray-600 dark:text-gray-400">Bienvenido, <?php echo e(auth()->user()->name); ?></p>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee): ?>
        <div class="grid gap-4 md:grid-cols-3">
            
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                        <?php if (isset($component)) { $__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.user','data' => ['class' => 'h-6 w-6 text-blue-600 dark:text-blue-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.user'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-blue-600 dark:text-blue-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff)): ?>
<?php $attributes = $__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff; ?>
<?php unset($__attributesOriginalcbe89caa4ae8c58f7efd0ed6343c35ff); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff)): ?>
<?php $component = $__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff; ?>
<?php unset($__componentOriginalcbe89caa4ae8c58f7efd0ed6343c35ff); ?>
<?php endif; ?>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white"><?php echo e($employee->full_name); ?></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">#<?php echo e($employee->employee_number); ?></p>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Posición:</span> <?php echo e($employee->position ?? 'No asignada'); ?>

                    </p>
                    <p class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Email:</span> <?php echo e($employee->email); ?>

                    </p>
                </div>
            </div>

            
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                        <?php if (isset($component)) { $__componentOriginal0d48bd54d72df81b49ee07c1a3735f04 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d48bd54d72df81b49ee07c1a3735f04 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.map-pin','data' => ['class' => 'h-6 w-6 text-green-600 dark:text-green-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.map-pin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-green-600 dark:text-green-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d48bd54d72df81b49ee07c1a3735f04)): ?>
<?php $attributes = $__attributesOriginal0d48bd54d72df81b49ee07c1a3735f04; ?>
<?php unset($__attributesOriginal0d48bd54d72df81b49ee07c1a3735f04); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d48bd54d72df81b49ee07c1a3735f04)): ?>
<?php $component = $__componentOriginal0d48bd54d72df81b49ee07c1a3735f04; ?>
<?php unset($__componentOriginal0d48bd54d72df81b49ee07c1a3735f04); ?>
<?php endif; ?>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Área</h3>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Nombre:</span> <?php echo e($employee->area?->name ?? 'No asignada'); ?>

                    </p>
                    <p class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Departamento:</span> <?php echo e($employee->area?->department?->name ?? 'N/A'); ?>

                    </p>
                </div>
            </div>

            
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                        <?php if (isset($component)) { $__componentOriginal4a4fffe04433d6d6be16f26ad2650578 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4a4fffe04433d6d6be16f26ad2650578 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.clock','data' => ['class' => 'h-6 w-6 text-purple-600 dark:text-purple-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.clock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-purple-600 dark:text-purple-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4a4fffe04433d6d6be16f26ad2650578)): ?>
<?php $attributes = $__attributesOriginal4a4fffe04433d6d6be16f26ad2650578; ?>
<?php unset($__attributesOriginal4a4fffe04433d6d6be16f26ad2650578); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4a4fffe04433d6d6be16f26ad2650578)): ?>
<?php $component = $__componentOriginal4a4fffe04433d6d6be16f26ad2650578; ?>
<?php unset($__componentOriginal4a4fffe04433d6d6be16f26ad2650578); ?>
<?php endif; ?>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Turno</h3>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Nombre:</span> <?php echo e($employee->shift?->name ?? 'No asignado'); ?>

                    </p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee->shift): ?>
                        <p class="text-gray-600 dark:text-gray-400">
                            <span class="font-medium">Horario:</span> 
                            <?php echo e(\Carbon\Carbon::parse($employee->shift->start_time)->format('H:i')); ?> - 
                            <?php echo e(\Carbon\Carbon::parse($employee->shift->end_time)->format('H:i')); ?>

                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acciones Rápidas</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <a href="<?php echo e(route('employee.profile')); ?>" wire:navigate
                    class="flex items-center gap-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 hover:bg-gray-50 dark:hover:bg-neutral-700 transition">
                    <?php if (isset($component)) { $__componentOriginal736a3246944d2d8ec1919ce8cba6f0a6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal736a3246944d2d8ec1919ce8cba6f0a6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.pencil-square','data' => ['class' => 'h-5 w-5 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.pencil-square'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal736a3246944d2d8ec1919ce8cba6f0a6)): ?>
<?php $attributes = $__attributesOriginal736a3246944d2d8ec1919ce8cba6f0a6; ?>
<?php unset($__attributesOriginal736a3246944d2d8ec1919ce8cba6f0a6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal736a3246944d2d8ec1919ce8cba6f0a6)): ?>
<?php $component = $__componentOriginal736a3246944d2d8ec1919ce8cba6f0a6; ?>
<?php unset($__componentOriginal736a3246944d2d8ec1919ce8cba6f0a6); ?>
<?php endif; ?>
                    <span class="text-gray-700 dark:text-gray-300">Editar Perfil</span>
                </a>
                <a href="<?php echo e(route('employee.settings.profile')); ?>" wire:navigate
                    class="flex items-center gap-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 hover:bg-gray-50 dark:hover:bg-neutral-700 transition">
                    <?php if (isset($component)) { $__componentOriginal85f9e6c3832e289a25340c3790632afa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal85f9e6c3832e289a25340c3790632afa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.cog-6-tooth','data' => ['class' => 'h-5 w-5 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.cog-6-tooth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal85f9e6c3832e289a25340c3790632afa)): ?>
<?php $attributes = $__attributesOriginal85f9e6c3832e289a25340c3790632afa; ?>
<?php unset($__attributesOriginal85f9e6c3832e289a25340c3790632afa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal85f9e6c3832e289a25340c3790632afa)): ?>
<?php $component = $__componentOriginal85f9e6c3832e289a25340c3790632afa; ?>
<?php unset($__componentOriginal85f9e6c3832e289a25340c3790632afa); ?>
<?php endif; ?>
                    <span class="text-gray-700 dark:text-gray-300">Configuración</span>
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="rounded-xl border border-yellow-200 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 p-6">
            <div class="flex items-center gap-3">
                <?php if (isset($component)) { $__componentOriginal7f0e8d69add49581695c1337b3f85fff = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7f0e8d69add49581695c1337b3f85fff = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.exclamation-triangle','data' => ['class' => 'h-6 w-6 text-yellow-600 dark:text-yellow-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.exclamation-triangle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-yellow-600 dark:text-yellow-400']); ?>
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
                    <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">Registro no encontrado</h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                        No se encontró un registro de empleado asociado a tu cuenta. Contacta al administrador.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views/livewire/employee/dashboard.blade.php ENDPATH**/ ?>