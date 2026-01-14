

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'external' => null,
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
    'external' => null,
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
    ->add('underline underline-offset-[6px] hover:decoration-current')
    ->add('decoration-zinc-800/20 dark:decoration-white/20')
    ;
?>

<a <?php echo e($attributes->class($classes)); ?> <?php if ($external) : ?>target="_blank"<?php endif; ?>><?php echo e($slot); ?></a><?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\callout\link.blade.php ENDPATH**/ ?>