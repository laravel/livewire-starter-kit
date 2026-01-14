

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'container' => null,
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
    'container' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if ($container): ?>
    <?php if (isset($component)) { $__componentOriginal39d28151b541c57956bc721586eadae9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal39d28151b541c57956bc721586eadae9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::container','data' => ['class' => ''.$attributes->get('class').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::container'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => ''.$attributes->get('class').'']); ?>
        <?php echo e($slot); ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal39d28151b541c57956bc721586eadae9)): ?>
<?php $attributes = $__attributesOriginal39d28151b541c57956bc721586eadae9; ?>
<?php unset($__attributesOriginal39d28151b541c57956bc721586eadae9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal39d28151b541c57956bc721586eadae9)): ?>
<?php $component = $__componentOriginal39d28151b541c57956bc721586eadae9; ?>
<?php unset($__componentOriginal39d28151b541c57956bc721586eadae9); ?>
<?php endif; ?>
<?php else: ?>
    <?php echo e($slot); ?>

<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\with-container.blade.php ENDPATH**/ ?>