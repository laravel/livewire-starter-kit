<?php if (isset($component)) { $__componentOriginalce5847ac41e2319cc94841d423efce16 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce5847ac41e2319cc94841d423efce16 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.auth.simple','data' => ['title' => $title ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.auth.simple'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title ?? null)]); ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce5847ac41e2319cc94841d423efce16)): ?>
<?php $attributes = $__attributesOriginalce5847ac41e2319cc94841d423efce16; ?>
<?php unset($__attributesOriginalce5847ac41e2319cc94841d423efce16); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce5847ac41e2319cc94841d423efce16)): ?>
<?php $component = $__componentOriginalce5847ac41e2319cc94841d423efce16; ?>
<?php unset($__componentOriginalce5847ac41e2319cc94841d423efce16); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views/components/layouts/auth.blade.php ENDPATH**/ ?>