<?php
$classes = Flux::classes()
    ->add('*:data-flux-field:mb-3')
    ->add('[&>[data-flux-field]:has(>[data-flux-description])]:mb-4')
    ->add('[&>[data-flux-field]:last-child]:mb-0!')
    ;

// Support adding the .self modifier to the wire:model directive...
if (($wireModel = $attributes->wire('model')) && $wireModel->directive && ! $wireModel->hasModifier('self')) {
    unset($attributes[$wireModel->directive]);

    $wireModel->directive .= '.self';

    $attributes = $attributes->merge([$wireModel->directive => $wireModel->value]);
}
?>

<?php if (isset($component)) { $__componentOriginal33e2911d6f1e72999cb4ebd3c5d00431 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal33e2911d6f1e72999cb4ebd3c5d00431 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::with-field','data' => ['attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::with-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
    <ui-checkbox-group <?php echo e($attributes->class($classes)); ?> data-flux-checkbox-group>
        <?php echo e($slot); ?>

    </ui-checkbox-group>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal33e2911d6f1e72999cb4ebd3c5d00431)): ?>
<?php $attributes = $__attributesOriginal33e2911d6f1e72999cb4ebd3c5d00431; ?>
<?php unset($__attributesOriginal33e2911d6f1e72999cb4ebd3c5d00431); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal33e2911d6f1e72999cb4ebd3c5d00431)): ?>
<?php $component = $__componentOriginal33e2911d6f1e72999cb4ebd3c5d00431; ?>
<?php unset($__componentOriginal33e2911d6f1e72999cb4ebd3c5d00431); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\checkbox\group\variants\default.blade.php ENDPATH**/ ?>