

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['attributes' => $attributes,'size' => $size === 'sm' || $size === 'xs' ? 'xs' : 'sm','xData' => '{ open: false }','xOn:click' => 'open = ! open; $el.closest(\'[data-flux-input]\').querySelector(\'input\').setAttribute(\'type\', open ? \'text\' : \'password\')','xBind:dataViewableOpen' => 'open','ariaLabel' => ''.e(__('Toggle password visibility')).'','xInit' => '
        let input = $el.closest(\'[data-flux-input]\')?.querySelector(\'input\');

        if (! input) return;

        let observer = new MutationObserver(() => {
            let type = open ? \'text\' : \'password\';
            if (input.getAttribute(\'type\') === type) return;
            input.setAttribute(\'type\', type)
        });

        observer.observe(input, { attributes: true, attributeFilter: [\'type\'] });
    ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes),'size' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($size === 'sm' || $size === 'xs' ? 'xs' : 'sm'),'x-data' => '{ open: false }','x-on:click' => 'open = ! open; $el.closest(\'[data-flux-input]\').querySelector(\'input\').setAttribute(\'type\', open ? \'text\' : \'password\')','x-bind:data-viewable-open' => 'open','aria-label' => ''.e(__('Toggle password visibility')).'','x-init' => '
        let input = $el.closest(\'[data-flux-input]\')?.querySelector(\'input\');

        if (! input) return;

        let observer = new MutationObserver(() => {
            let type = open ? \'text\' : \'password\';
            if (input.getAttribute(\'type\') === type) return;
            input.setAttribute(\'type\', type)
        });

        observer.observe(input, { attributes: true, attributeFilter: [\'type\'] });
    ']); ?>
    <?php if (isset($component)) { $__componentOriginale550709c881a3d29c31af0c4e9681688 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale550709c881a3d29c31af0c4e9681688 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.eye-slash','data' => ['variant' => 'micro','class' => 'hidden [[data-viewable-open]>&]:block']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.eye-slash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro','class' => 'hidden [[data-viewable-open]>&]:block']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale550709c881a3d29c31af0c4e9681688)): ?>
<?php $attributes = $__attributesOriginale550709c881a3d29c31af0c4e9681688; ?>
<?php unset($__attributesOriginale550709c881a3d29c31af0c4e9681688); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale550709c881a3d29c31af0c4e9681688)): ?>
<?php $component = $__componentOriginale550709c881a3d29c31af0c4e9681688; ?>
<?php unset($__componentOriginale550709c881a3d29c31af0c4e9681688); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal2e57535a42d25d5415c31aa83132341b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2e57535a42d25d5415c31aa83132341b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.eye','data' => ['variant' => 'micro','class' => 'block [[data-viewable-open]>&]:hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.eye'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'micro','class' => 'block [[data-viewable-open]>&]:hidden']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2e57535a42d25d5415c31aa83132341b)): ?>
<?php $attributes = $__attributesOriginal2e57535a42d25d5415c31aa83132341b; ?>
<?php unset($__attributesOriginal2e57535a42d25d5415c31aa83132341b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2e57535a42d25d5415c31aa83132341b)): ?>
<?php $component = $__componentOriginal2e57535a42d25d5415c31aa83132341b; ?>
<?php unset($__componentOriginal2e57535a42d25d5415c31aa83132341b); ?>
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
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\input\viewable.blade.php ENDPATH**/ ?>