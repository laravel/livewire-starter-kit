<?php if (isset($component)) { $__componentOriginalae07e75fee762332f0b84bd00881cf75 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalae07e75fee762332f0b84bd00881cf75 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.employee.sidebar','data' => ['title' => $title ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.employee.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title ?? null)]); ?>
    <?php if (isset($component)) { $__componentOriginal95c5505ccad18880318521d2bba3eac7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95c5505ccad18880318521d2bba3eac7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::main','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::main'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <?php echo e($slot); ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95c5505ccad18880318521d2bba3eac7)): ?>
<?php $attributes = $__attributesOriginal95c5505ccad18880318521d2bba3eac7; ?>
<?php unset($__attributesOriginal95c5505ccad18880318521d2bba3eac7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95c5505ccad18880318521d2bba3eac7)): ?>
<?php $component = $__componentOriginal95c5505ccad18880318521d2bba3eac7; ?>
<?php unset($__componentOriginal95c5505ccad18880318521d2bba3eac7); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalae07e75fee762332f0b84bd00881cf75)): ?>
<?php $attributes = $__attributesOriginalae07e75fee762332f0b84bd00881cf75; ?>
<?php unset($__attributesOriginalae07e75fee762332f0b84bd00881cf75); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalae07e75fee762332f0b84bd00881cf75)): ?>
<?php $component = $__componentOriginalae07e75fee762332f0b84bd00881cf75; ?>
<?php unset($__componentOriginalae07e75fee762332f0b84bd00881cf75); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views\components\layouts\employee.blade.php ENDPATH**/ ?>