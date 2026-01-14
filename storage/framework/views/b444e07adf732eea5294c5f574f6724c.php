<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,
    'variant' => null,
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
    'name' => null,
    'variant' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
// We only want to show the name attribute it has been set manually
// but not if it has been set from the `wire:model` attribute...
$showName = isset($name);
if (! isset($name)) {
    $name = $attributes->whereStartsWith('wire:model')->first();
}

$classes = Flux::classes()
    // Adjust spacing between fields...
    ->add('*:data-flux-field:mb-3')
    ->add('[&>[data-flux-field]:has(>[data-flux-description])]:mb-4')
    ->add('[&>[data-flux-field]:last-child]:mb-0!')
    ;
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
    <ui-radio-group <?php echo e($attributes->class($classes)); ?> <?php if($showName): ?> name="<?php echo e($name); ?>" <?php endif; ?> data-flux-radio-group>
        <?php echo e($slot); ?>

    </ui-radio-group>
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
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\radio\group\variants\default.blade.php ENDPATH**/ ?>