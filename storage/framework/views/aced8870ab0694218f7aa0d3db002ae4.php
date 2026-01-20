

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'orientation' => null,
    'vertical' => false,
    'variant' => null,
    'faint' => false,
    'text' => null,
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
    'orientation' => null,
    'vertical' => false,
    'variant' => null,
    'faint' => false,
    'text' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$orientation ??= $vertical ? 'vertical' : 'horizontal';

$classes = Flux::classes('border-0 [print-color-adjust:exact]')
    ->add(match ($variant) {
        'subtle' => 'bg-zinc-800/5 dark:bg-white/10',
        default => 'bg-zinc-800/15 dark:bg-white/20',
    })
    ->add(match ($orientation) {
        'horizontal' => 'h-px w-full',
        'vertical' => 'self-stretch self-center w-px',
    })
    ;
?>

<?php if ($text): ?>
    <div data-orientation="<?php echo e($orientation); ?>" class="flex items-center w-full" role="none" data-flux-separator>
        <div <?php echo e($attributes->class([$classes, 'grow'])); ?>></div>

        <span class="shrink mx-6 font-medium text-sm text-zinc-500 dark:text-zinc-300 whitespace-nowrap"><?php echo e($text); ?></span>

        <div <?php echo e($attributes->class([$classes, 'grow'])); ?>></div>
    </div>
<?php else: ?>
    <div data-orientation="<?php echo e($orientation); ?>" role="none" <?php echo e($attributes->class($classes, 'shrink-0')); ?> data-flux-separator></div>
<?php endif; ?>
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\vendor\livewire\flux\src/../stubs/resources/views/flux/separator.blade.php ENDPATH**/ ?>