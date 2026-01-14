

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'legend' => null,
    'description' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'legend' => null,
    'description' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$classes = Flux::classes()
    ->add('[&[disabled]_[data-flux-label]]:opacity-50') // Dim labels when the fieldset is disabled...
    ->add('[&[disabled]_[data-flux-legend]]:opacity-50') // Dim legend when the fieldset is disabled...

    // Adjust spacing between fields...
    ->add('*:data-flux-field:mb-3')

    // Adjust spacing between fields...
    ->add('*:data-flux-field:mb-3')
    ->add('[&>[data-flux-field]:has(>[data-flux-description])]:mb-4')
    ->add('[&>[data-flux-field]:last-child]:mb-0!')

    // Adjust spacing below legend...
    ->add('[&>legend]:mb-4')
    ->add('[&>legend:has(+[data-flux-description])]:mb-2')

    // Adjust spacing below description...
    ->add('[&>legend+[data-flux-description]]:mb-4')
    ;
?>

<fieldset <?php echo e($attributes->class($classes)); ?> data-flux-fieldset>
    <?php if ($legend): ?>
        <?php if (isset($component)) { $__componentOriginal7f5ad8f19de0f492bb71dc13ca0a19cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7f5ad8f19de0f492bb71dc13ca0a19cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::legend','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::legend'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e($legend); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7f5ad8f19de0f492bb71dc13ca0a19cd)): ?>
<?php $attributes = $__attributesOriginal7f5ad8f19de0f492bb71dc13ca0a19cd; ?>
<?php unset($__attributesOriginal7f5ad8f19de0f492bb71dc13ca0a19cd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7f5ad8f19de0f492bb71dc13ca0a19cd)): ?>
<?php $component = $__componentOriginal7f5ad8f19de0f492bb71dc13ca0a19cd; ?>
<?php unset($__componentOriginal7f5ad8f19de0f492bb71dc13ca0a19cd); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if ($description): ?>
        <?php if (isset($component)) { $__componentOriginalf323826200b199a8f33f16501b918a9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf323826200b199a8f33f16501b918a9a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::description','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::description'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e($description); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf323826200b199a8f33f16501b918a9a)): ?>
<?php $attributes = $__attributesOriginalf323826200b199a8f33f16501b918a9a; ?>
<?php unset($__attributesOriginalf323826200b199a8f33f16501b918a9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf323826200b199a8f33f16501b918a9a)): ?>
<?php $component = $__componentOriginalf323826200b199a8f33f16501b918a9a; ?>
<?php unset($__componentOriginalf323826200b199a8f33f16501b918a9a); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php echo e($slot); ?>

</fieldset>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\fieldset.blade.php ENDPATH**/ ?>