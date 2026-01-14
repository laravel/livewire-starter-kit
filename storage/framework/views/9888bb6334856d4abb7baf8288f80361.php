

<?php
$classes = Flux::classes('[grid-area:footer]')
    ->add($attributes->has('container') ? '' : 'p-6 lg:p-8')
    ;
?>

<div <?php echo e($attributes->class($classes)); ?> data-flux-footer>
    <?php if (isset($component)) { $__componentOriginal79d74f4a8c7810243bbc2336a1f589de = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79d74f4a8c7810243bbc2336a1f589de = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::with-container','data' => ['attributes' => $attributes->except('class')->class('p-6 lg:p-8')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::with-container'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->except('class')->class('p-6 lg:p-8'))]); ?>
        <?php echo e($slot); ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79d74f4a8c7810243bbc2336a1f589de)): ?>
<?php $attributes = $__attributesOriginal79d74f4a8c7810243bbc2336a1f589de; ?>
<?php unset($__attributesOriginal79d74f4a8c7810243bbc2336a1f589de); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79d74f4a8c7810243bbc2336a1f589de)): ?>
<?php $component = $__componentOriginal79d74f4a8c7810243bbc2336a1f589de; ?>
<?php unset($__componentOriginal79d74f4a8c7810243bbc2336a1f589de); ?>
<?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\footer.blade.php ENDPATH**/ ?>