

<?php
extract(Flux::forwardedAttributes($attributes, [
    'tooltipPosition',
    'tooltipKbd',
    'tooltip',
]));
?>

<?php $tooltipPosition = $tooltipPosition ??= $attributes->pluck('tooltip:position'); ?>
<?php $tooltipKbd = $tooltipKbd ??= $attributes->pluck('tooltip:kbd'); ?>
<?php $tooltip = $tooltip ??= $attributes->pluck('tooltip'); ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'tooltipPosition' => 'top',
    'tooltipKbd' => null,
    'tooltip' => null,
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
    'tooltipPosition' => 'top',
    'tooltipKbd' => null,
    'tooltip' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if ($tooltip): ?>
    <?php if (isset($component)) { $__componentOriginalf5109f209df079b3a83484e1e6310749 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5109f209df079b3a83484e1e6310749 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::tooltip.index','data' => ['content' => $tooltip,'position' => $tooltipPosition,'kbd' => $tooltipKbd]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['content' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tooltip),'position' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tooltipPosition),'kbd' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tooltipKbd)]); ?>
        <?php echo e($slot); ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf5109f209df079b3a83484e1e6310749)): ?>
<?php $attributes = $__attributesOriginalf5109f209df079b3a83484e1e6310749; ?>
<?php unset($__attributesOriginalf5109f209df079b3a83484e1e6310749); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf5109f209df079b3a83484e1e6310749)): ?>
<?php $component = $__componentOriginalf5109f209df079b3a83484e1e6310749; ?>
<?php unset($__componentOriginalf5109f209df079b3a83484e1e6310749); ?>
<?php endif; ?>
<?php else: ?>
    <?php echo e($slot); ?>

<?php endif; ?>
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\vendor\livewire\flux\src/../stubs/resources/views/flux/with-tooltip.blade.php ENDPATH**/ ?>