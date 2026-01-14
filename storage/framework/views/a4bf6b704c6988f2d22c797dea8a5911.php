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

<?php if (!Flux::componentExists($name = 'checkbox.group.variants.' . $variant)) throw new \Exception("Flux component [{$name}] does not exist."); ?><?php if (isset($component)) { $__componentOriginal4c7f570a6fa628b9904d8cf142be6c8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4c7f570a6fa628b9904d8cf142be6c8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve([
    'view' => (app()->version() >= 12 ? hash('xxh128', 'flux') : md5('flux')) . '::' . 'checkbox.group.variants.' . $variant,
    'data' => $__env->getCurrentComponentData(),
] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::' . 'checkbox.group.variants.' . $variant); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes($attributes->getAttributes()); ?><?php echo e($slot); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4c7f570a6fa628b9904d8cf142be6c8b)): ?>
<?php $attributes = $__attributesOriginal4c7f570a6fa628b9904d8cf142be6c8b; ?>
<?php unset($__attributesOriginal4c7f570a6fa628b9904d8cf142be6c8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4c7f570a6fa628b9904d8cf142be6c8b)): ?>
<?php $component = $__componentOriginal4c7f570a6fa628b9904d8cf142be6c8b; ?>
<?php unset($__componentOriginal4c7f570a6fa628b9904d8cf142be6c8b); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\checkbox\group\index.blade.php ENDPATH**/ ?>