

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'as' => null,
    'external' => null,
    'accent' => true,
    'variant' => null,
    'strong' => false,
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
    'as' => null,
    'external' => null,
    'accent' => true,
    'variant' => null,
    'strong' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$classes = Flux::classes()
    ->add('inline font-medium')
    ->add('underline-offset-[6px] hover:decoration-current')
    ->add(match ($variant) {
        'ghost' => 'no-underline hover:underline',
        'subtle' => 'no-underline',
        default => 'underline',
    })
    ->add('[[data-color]>&]:text-inherit [[data-color]>&]:decoration-current/20 dark:[[data-color]>&]:decoration-current/50 [[data-color]>&]:hover:decoration-current')
    ->add(match ($variant) {
        'subtle' => 'text-zinc-500 dark:text-white/70 hover:text-zinc-800 dark:hover:text-white',
        default => match ($accent) {
            true => 'text-[var(--color-accent-content)] decoration-[color-mix(in_oklab,var(--color-accent-content),transparent_80%)]',
            false => 'text-zinc-800 dark:text-white decoration-zinc-800/20 dark:decoration-white/20',
        },
    })
    ;
?>

<?php if ($as !== 'button') : ?><a <?php echo e($attributes->class($classes)); ?> data-flux-link <?php if ($external) : ?>target="_blank"<?php endif; ?>><?php echo e($slot); ?></a><?php else : ?><button <?php echo e($attributes->merge(['class' => $classes, 'type' => 'button'])); ?> data-flux-link><?php echo e($slot); ?></button><?php endif; ?><?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\link.blade.php ENDPATH**/ ?>