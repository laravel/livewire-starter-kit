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
$variant = $variant !== 'default' && Flux::componentExists('select.variants.' . $variant)
    ? 'custom'
    : 'default';
?>

<?php if (!Flux::componentExists($name = 'select.option.variants.' . $variant)) throw new \Exception("Flux component [{$name}] does not exist."); ?><?php if (isset($component)) { $__componentOriginal0c793dac3b0f3858ef7a5fdb4e001894 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0c793dac3b0f3858ef7a5fdb4e001894 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve([
    'view' => (app()->version() >= 12 ? hash('xxh128', 'flux') : md5('flux')) . '::' . 'select.option.variants.' . $variant,
    'data' => $__env->getCurrentComponentData(),
] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::' . 'select.option.variants.' . $variant); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes($attributes->getAttributes()); ?><?php echo e($slot); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0c793dac3b0f3858ef7a5fdb4e001894)): ?>
<?php $attributes = $__attributesOriginal0c793dac3b0f3858ef7a5fdb4e001894; ?>
<?php unset($__attributesOriginal0c793dac3b0f3858ef7a5fdb4e001894); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0c793dac3b0f3858ef7a5fdb4e001894)): ?>
<?php $component = $__componentOriginal0c793dac3b0f3858ef7a5fdb4e001894; ?>
<?php unset($__componentOriginal0c793dac3b0f3858ef7a5fdb4e001894); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\select\option\index.blade.php ENDPATH**/ ?>