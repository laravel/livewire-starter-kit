

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
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
// We only want to show the name attribute on the checkbox if it has been set
// manually, but not if it has been set from the wire:model attribute...
$showName = isset($name);

if (! isset($name)) {
    $name = $attributes->whereStartsWith('wire:model')->first();
}

$classes = Flux::classes()
    ->add('flex size-[1.125rem] rounded-[.3rem] mt-px outline-offset-2')
    ;
?>

<?php if (isset($component)) { $__componentOriginalaa38908a80414b887e964866233e69a0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa38908a80414b887e964866233e69a0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::with-inline-field','data' => ['attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::with-inline-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
    <ui-checkbox <?php echo e($attributes->class($classes)); ?> <?php if($showName): ?> name="<?php echo e($name); ?>" <?php endif; ?> data-flux-control data-flux-checkbox>
        <?php if (isset($component)) { $__componentOriginal132809635db4ae0903491272a3f385e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal132809635db4ae0903491272a3f385e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::checkbox.indicator','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::checkbox.indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal132809635db4ae0903491272a3f385e8)): ?>
<?php $attributes = $__attributesOriginal132809635db4ae0903491272a3f385e8; ?>
<?php unset($__attributesOriginal132809635db4ae0903491272a3f385e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal132809635db4ae0903491272a3f385e8)): ?>
<?php $component = $__componentOriginal132809635db4ae0903491272a3f385e8; ?>
<?php unset($__componentOriginal132809635db4ae0903491272a3f385e8); ?>
<?php endif; ?>
    </ui-checkbox>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa38908a80414b887e964866233e69a0)): ?>
<?php $attributes = $__attributesOriginalaa38908a80414b887e964866233e69a0; ?>
<?php unset($__attributesOriginalaa38908a80414b887e964866233e69a0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa38908a80414b887e964866233e69a0)): ?>
<?php $component = $__componentOriginalaa38908a80414b887e964866233e69a0; ?>
<?php unset($__componentOriginalaa38908a80414b887e964866233e69a0); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\src/../stubs/resources/views/flux/checkbox/variants/default.blade.php ENDPATH**/ ?>