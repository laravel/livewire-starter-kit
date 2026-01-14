



<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => null,
    'name' => null,
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
    'icon' => null,
    'name' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$icon = $name ?? $icon;
?>

<?php if (!Flux::componentExists($name = 'icon.' . $icon)) throw new \Exception("Flux component [{$name}] does not exist."); ?><?php if (isset($component)) { $__componentOriginal99f5bdde02e072cb5fe2c95dd124b389 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99f5bdde02e072cb5fe2c95dd124b389 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve([
    'view' => (app()->version() >= 12 ? hash('xxh128', 'flux') : md5('flux')) . '::' . 'icon.' . $icon,
    'data' => $__env->getCurrentComponentData(),
] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::' . 'icon.' . $icon); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes($attributes->getAttributes()); ?><?php echo e($slot); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99f5bdde02e072cb5fe2c95dd124b389)): ?>
<?php $attributes = $__attributesOriginal99f5bdde02e072cb5fe2c95dd124b389; ?>
<?php unset($__attributesOriginal99f5bdde02e072cb5fe2c95dd124b389); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99f5bdde02e072cb5fe2c95dd124b389)): ?>
<?php $component = $__componentOriginal99f5bdde02e072cb5fe2c95dd124b389; ?>
<?php unset($__componentOriginal99f5bdde02e072cb5fe2c95dd124b389); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\icon\index.blade.php ENDPATH**/ ?>