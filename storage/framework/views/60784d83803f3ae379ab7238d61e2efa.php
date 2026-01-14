

<?php foreach (([ 'variant' ]) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default',
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
    'variant' => 'default',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
// This prevents variants picked up by `@aware()` from other wrapping components like flux::modal from being used here...
$variant = $variant !== 'default' && Flux::componentExists('radio.variants.' . $variant)
    ? $variant
    : 'default';
?>

<?php if (!Flux::componentExists($name = 'radio.variants.' . $variant)) throw new \Exception("Flux component [{$name}] does not exist."); ?><?php if (isset($component)) { $__componentOriginal265f91d25e15cdf8789a0aea59dc939c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal265f91d25e15cdf8789a0aea59dc939c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve([
    'view' => (app()->version() >= 12 ? hash('xxh128', 'flux') : md5('flux')) . '::' . 'radio.variants.' . $variant,
    'data' => $__env->getCurrentComponentData(),
] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::' . 'radio.variants.' . $variant); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes($attributes->getAttributes()); ?><?php echo e($slot); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal265f91d25e15cdf8789a0aea59dc939c)): ?>
<?php $attributes = $__attributesOriginal265f91d25e15cdf8789a0aea59dc939c; ?>
<?php unset($__attributesOriginal265f91d25e15cdf8789a0aea59dc939c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal265f91d25e15cdf8789a0aea59dc939c)): ?>
<?php $component = $__componentOriginal265f91d25e15cdf8789a0aea59dc939c; ?>
<?php unset($__componentOriginal265f91d25e15cdf8789a0aea59dc939c); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\radio\index.blade.php ENDPATH**/ ?>