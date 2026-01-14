<?php if (isset($component)) { $__componentOriginal09d149b94538c2315f503a5e890f2640 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal09d149b94538c2315f503a5e890f2640 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.employee','data' => ['title' => __('Employee Dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.employee'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Employee Dashboard'))]); ?>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Panel de Empleado</h1>
            <p class="text-gray-600 dark:text-gray-400">Bienvenido a tu panel personal</p>
        </div>
        
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Mi Perfil</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Ver y editar tu información</p>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Horarios</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Consultar tus horarios</p>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Asistencia</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Registro de asistencia</p>
            </div>
        </div>
        
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <?php if (isset($component)) { $__componentOriginal1e4630c5daeca7ac226f30794c203a2d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e4630c5daeca7ac226f30794c203a2d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.placeholder-pattern','data' => ['class' => 'absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('placeholder-pattern'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e4630c5daeca7ac226f30794c203a2d)): ?>
<?php $attributes = $__attributesOriginal1e4630c5daeca7ac226f30794c203a2d; ?>
<?php unset($__attributesOriginal1e4630c5daeca7ac226f30794c203a2d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e4630c5daeca7ac226f30794c203a2d)): ?>
<?php $component = $__componentOriginal1e4630c5daeca7ac226f30794c203a2d; ?>
<?php unset($__componentOriginal1e4630c5daeca7ac226f30794c203a2d); ?>
<?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal09d149b94538c2315f503a5e890f2640)): ?>
<?php $attributes = $__attributesOriginal09d149b94538c2315f503a5e890f2640; ?>
<?php unset($__attributesOriginal09d149b94538c2315f503a5e890f2640); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal09d149b94538c2315f503a5e890f2640)): ?>
<?php $component = $__componentOriginal09d149b94538c2315f503a5e890f2640; ?>
<?php unset($__componentOriginal09d149b94538c2315f503a5e890f2640); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views\employee\dashboard.blade.php ENDPATH**/ ?>