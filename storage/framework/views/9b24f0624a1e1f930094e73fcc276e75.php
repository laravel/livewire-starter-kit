

<?php
$attributes = $attributes->merge([
    'variant' => 'subtle',
    'class' => '-me-1',
    'square' => true,
    'size' => null,
]);
?>

<?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['attributes' => $attributes,'size' => $size === 'sm' || $size === 'xs' ? 'xs' : 'sm','xData' => '{ copied: false }','xOn:click' => 'copied = ! copied; navigator.clipboard && navigator.clipboard.writeText($el.closest(\'[data-flux-input]\').querySelector(\'input\').value); setTimeout(() => copied = false, 2000)','xBind:dataCopyableCopied' => 'copied','ariaLabel' => ''.e(__('Copy to clipboard')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes),'size' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($size === 'sm' || $size === 'xs' ? 'xs' : 'sm'),'x-data' => '{ copied: false }','x-on:click' => 'copied = ! copied; navigator.clipboard && navigator.clipboard.writeText($el.closest(\'[data-flux-input]\').querySelector(\'input\').value); setTimeout(() => copied = false, 2000)','x-bind:data-copyable-copied' => 'copied','aria-label' => ''.e(__('Copy to clipboard')).'']); ?>
    <?php if (isset($component)) { $__componentOriginald1623caf8352e929ab8330cb6301c6be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald1623caf8352e929ab8330cb6301c6be = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.clipboard-document-check','data' => ['variant' => 'mini','class' => 'hidden [[data-copyable-copied]>&]:block']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.clipboard-document-check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'mini','class' => 'hidden [[data-copyable-copied]>&]:block']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald1623caf8352e929ab8330cb6301c6be)): ?>
<?php $attributes = $__attributesOriginald1623caf8352e929ab8330cb6301c6be; ?>
<?php unset($__attributesOriginald1623caf8352e929ab8330cb6301c6be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald1623caf8352e929ab8330cb6301c6be)): ?>
<?php $component = $__componentOriginald1623caf8352e929ab8330cb6301c6be; ?>
<?php unset($__componentOriginald1623caf8352e929ab8330cb6301c6be); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal4da0fb5c2f91d5abdd541ee46e42b692 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4da0fb5c2f91d5abdd541ee46e42b692 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.clipboard-document','data' => ['variant' => 'mini','class' => 'block [[data-copyable-copied]>&]:hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.clipboard-document'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'mini','class' => 'block [[data-copyable-copied]>&]:hidden']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4da0fb5c2f91d5abdd541ee46e42b692)): ?>
<?php $attributes = $__attributesOriginal4da0fb5c2f91d5abdd541ee46e42b692; ?>
<?php unset($__attributesOriginal4da0fb5c2f91d5abdd541ee46e42b692); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4da0fb5c2f91d5abdd541ee46e42b692)): ?>
<?php $component = $__componentOriginal4da0fb5c2f91d5abdd541ee46e42b692; ?>
<?php unset($__componentOriginal4da0fb5c2f91d5abdd541ee46e42b692); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\input\copyable.blade.php ENDPATH**/ ?>