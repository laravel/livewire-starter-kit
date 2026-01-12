

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'sticky' => null,
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
    'sticky' => null,
    'container' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$classes = Flux::classes('[grid-area:header]')
    ->add('z-10 min-h-14')
    ->add($container ? '' : 'flex items-center px-6 lg:px-8')
    ;

if ($sticky) {
    $attributes = $attributes->merge([
        'x-data' => '',
        'x-bind:style' => '{ position: \'sticky\', top: $el.offsetTop + \'px\', \'max-height\': \'calc(100vh - \' + $el.offsetTop + \'px)\' }',
    ]);
}
?>

<header <?php echo e($attributes->class($classes)); ?> data-flux-header>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($container): ?>
        <div class="mx-auto w-full h-full [:where(&)]:max-w-7xl px-6 lg:px-8 flex items-center">
            <?php echo e($slot); ?>

        </div>
    <?php else: ?>
        <?php echo e($slot); ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</header>
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\vendor\livewire\flux\src/../stubs/resources/views/flux/header.blade.php ENDPATH**/ ?>