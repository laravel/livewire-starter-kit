

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
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
    'name' => $attributes->whereStartsWith('wire:model')->first(),
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginalaa38908a80414b887e964866233e69a0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa38908a80414b887e964866233e69a0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::with-inline-field','data' => ['variant' => 'inline','attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::with-inline-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'inline','attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
    
    
    
    <ui-radio <?php echo e($attributes->class('flex size-[1.125rem] rounded-full mt-px outline-offset-2')); ?> data-flux-control data-flux-radio tabindex="-1">
        <?php if (isset($component)) { $__componentOriginald3bd261c5373ff9954a38b731d107a2d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3bd261c5373ff9954a38b731d107a2d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::radio.indicator','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::radio.indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3bd261c5373ff9954a38b731d107a2d)): ?>
<?php $attributes = $__attributesOriginald3bd261c5373ff9954a38b731d107a2d; ?>
<?php unset($__attributesOriginald3bd261c5373ff9954a38b731d107a2d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3bd261c5373ff9954a38b731d107a2d)): ?>
<?php $component = $__componentOriginald3bd261c5373ff9954a38b731d107a2d; ?>
<?php unset($__componentOriginald3bd261c5373ff9954a38b731d107a2d); ?>
<?php endif; ?>
    </ui-radio>
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
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\radio\variants\default.blade.php ENDPATH**/ ?>