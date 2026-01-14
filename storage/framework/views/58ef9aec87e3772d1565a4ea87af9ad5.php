<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,
    'align' => 'right',
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
    'align' => 'right',
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
    ->add('group h-5 w-8 min-w-8 relative inline-flex items-center outline-offset-2')
    ->add('rounded-full')
    ->add('transition')
    ->add('bg-zinc-800/15 [&[disabled]]:opacity-50 dark:bg-transparent dark:border dark:border-white/20 dark:[&[disabled]]:border-white/10')
    ->add('[print-color-adjust:exact]')
    ->add([
        'data-checked:bg-(--color-accent)',
        'data-checked:border-0',
    ])
    ;

$indicatorClasses = Flux::classes()
    ->add('size-3.5')
    ->add('rounded-full')
    ->add('transition translate-x-[3px] dark:translate-x-[2px] rtl:-translate-x-[3px] dark:rtl:-translate-x-[2px]')
    ->add('bg-white')
    ->add([
        'group-data-checked:translate-x-[15px] rtl:group-data-checked:-translate-x-[15px]',
        // We have to add the dark variant of the `translate-x-[15px]` to ensure that if `.dark` is added to an element mid way 
        // down the DOM instead of on the root HTML element, that the above `dark:translate-x-[2px]` doesn't over ride it...
        'dark:group-data-checked:translate-x-[15px] dark:rtl:group-data-checked:-translate-x-[15px]',
        'group-data-checked:bg-(--color-accent-foreground)',
    ]);
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($align === 'left' || $align === 'start'): ?>
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
        <ui-switch <?php echo e($attributes->class($classes)); ?> <?php if($showName): ?> name="<?php echo e($name); ?>" <?php endif; ?> data-flux-control data-flux-switch>
            <span class="<?php echo e(\Illuminate\Support\Arr::toCssClasses($indicatorClasses)); ?>"></span>
        </ui-switch>
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
<?php else: ?>
    <?php if (isset($component)) { $__componentOriginal1d609ace4b6b0700d4da371cfad55492 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1d609ace4b6b0700d4da371cfad55492 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::with-reversed-inline-field','data' => ['attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::with-reversed-inline-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
        <ui-switch <?php echo e($attributes->class($classes)); ?> <?php if($showName): ?> name="<?php echo e($name); ?>" <?php endif; ?> data-flux-control data-flux-switch>
            <span class="<?php echo e(\Illuminate\Support\Arr::toCssClasses($indicatorClasses)); ?>"></span>
        </ui-switch>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1d609ace4b6b0700d4da371cfad55492)): ?>
<?php $attributes = $__attributesOriginal1d609ace4b6b0700d4da371cfad55492; ?>
<?php unset($__attributesOriginal1d609ace4b6b0700d4da371cfad55492); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1d609ace4b6b0700d4da371cfad55492)): ?>
<?php $component = $__componentOriginal1d609ace4b6b0700d4da371cfad55492; ?>
<?php unset($__componentOriginal1d609ace4b6b0700d4da371cfad55492); ?>
<?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\switch.blade.php ENDPATH**/ ?>