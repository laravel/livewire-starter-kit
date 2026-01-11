

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'collapsible' => null,
    'stashable' => null, // @deprecated
    'sticky' => null,
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
    'collapsible' => null,
    'stashable' => null, // @deprecated
    'sticky' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$collapsibleOnMobile = $stashable || $collapsible === 'mobile' || $collapsible === true;

if ($stashable && $collapsible === null) {
    $collapsible = 'mobile';
}

$classes = Flux::classes('[grid-area:sidebar]')
    ->add('z-1 flex flex-col gap-4 [:where(&)]:w-64 p-4')
    ->add('data-flux-sidebar-collapsed-desktop:w-14 data-flux-sidebar-collapsed-desktop:px-2')
    ->add('data-flux-sidebar-collapsed-desktop:cursor-e-resize rtl:data-flux-sidebar-collapsed-desktop:cursor-w-resize')
    ;

if ($sticky) {
    $attributes = $attributes->merge([
        'class' => 'max-h-dvh overflow-y-auto overscroll-contain',
    ]);
}

if ($collapsibleOnMobile) {
    $attributes = $attributes->merge([
        // Prevent mobile sidebar from transitioning out on load...
        'x-init' => '$el.classList.add(\'transition-transform\')',
    ])->class([
        // Prevent mobile sidebar from flashing on-load...
        'max-lg:data-flux-sidebar-cloak:hidden',
        'data-flux-sidebar-on-mobile:data-flux-sidebar-collapsed-mobile:-translate-x-full data-flux-sidebar-on-mobile:data-flux-sidebar-collapsed-mobile:rtl:translate-x-full',
        'z-20! data-flux-sidebar-on-mobile:start-0! data-flux-sidebar-on-mobile:fixed! data-flux-sidebar-on-mobile:top-0! data-flux-sidebar-on-mobile:min-h-dvh! data-flux-sidebar-on-mobile:max-h-dvh!'
    ]);
}
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($collapsibleOnMobile): ?>
    <?php if (isset($component)) { $__componentOriginalb789a575a15f5f184ee0fb1f2cfab391 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb789a575a15f5f184ee0fb1f2cfab391 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.backdrop','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.backdrop'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb789a575a15f5f184ee0fb1f2cfab391)): ?>
<?php $attributes = $__attributesOriginalb789a575a15f5f184ee0fb1f2cfab391; ?>
<?php unset($__attributesOriginalb789a575a15f5f184ee0fb1f2cfab391); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb789a575a15f5f184ee0fb1f2cfab391)): ?>
<?php $component = $__componentOriginalb789a575a15f5f184ee0fb1f2cfab391; ?>
<?php unset($__componentOriginalb789a575a15f5f184ee0fb1f2cfab391); ?>
<?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<ui-sidebar
    <?php echo e($attributes->class($classes)); ?>

    <?php if($collapsible): ?> collapsible="<?php echo e($collapsible === 'mobile' ? 'mobile' : 'true'); ?>" <?php endif; ?>
    <?php if($stashable): ?> stashable <?php endif; ?>
    <?php if($sticky): ?> sticky <?php endif; ?>
    x-data
    data-flux-sidebar-cloak
    data-flux-sidebar
>
    <?php echo e($slot); ?>

</ui-sidebar>
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\vendor\livewire\flux\src/../stubs/resources/views/flux/sidebar/index.blade.php ENDPATH**/ ?>