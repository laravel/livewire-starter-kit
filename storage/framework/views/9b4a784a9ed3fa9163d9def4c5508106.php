

<?php foreach ((['animate' => null]) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'animate' => null,
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
    'animate' => null,
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
        ->add('[:where(&)]:h-4 [:where(&)]:rounded-md [:where(&)]:bg-zinc-400/20')
        ->add(match ($animate) {
            'shimmer' => [
                'relative before:absolute before:inset-0 before:-translate-x-full',
                'overflow-hidden isolate',
                '[:where(&)]:[--flux-shimmer-color:white]',
                'dark:[:where(&)]:[--flux-shimmer-color:var(--color-zinc-900)]',
                'before:z-10 before:animate-[flux-shimmer_2s_infinite]',
                'before:bg-gradient-to-r before:from-transparent before:via-[var(--flux-shimmer-color)]/50 dark:before:via-[var(--flux-shimmer-color)]/50 before:to-transparent',
            ],
            'pulse' => 'animate-pulse',
            default => '',
        })
        ;
?>

<div <?php echo e($attributes->class($classes)); ?> data-flux-skeleton>
    <?php echo e($slot); ?>

</div><?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\skeleton\index.blade.php ENDPATH**/ ?>