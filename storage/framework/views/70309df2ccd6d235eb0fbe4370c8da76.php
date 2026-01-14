

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'inline' => false,
    'variant' => null,
    'color' => null,
    'size' => null,
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
    'inline' => false,
    'variant' => null,
    'color' => null,
    'size' => null,
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
    ->add(match ($size) {
        'xl' => 'text-lg',
        'lg' => 'text-base',
        default => '[:where(&)]:text-sm',
        'sm' => 'text-xs',
    })
    ->add($color ? match($color) {
        'red' => 'text-red-600 dark:text-red-400',
        'orange' => 'text-orange-600 dark:text-orange-400',
        'amber' => 'text-amber-600 dark:text-amber-500',
        'yellow' => 'text-yellow-600 dark:text-yellow-500',
        'lime' => 'text-lime-600 dark:text-lime-500',
        'green' => 'text-green-600 dark:text-green-500',
        'emerald' => 'text-emerald-600 dark:text-emerald-400',
        'teal' => 'text-teal-600 dark:text-teal-400',
        'cyan' => 'text-cyan-600 dark:text-cyan-400',
        'sky' => 'text-sky-600 dark:text-sky-400',
        'blue' => 'text-blue-600 dark:text-blue-400',
        'indigo' => 'text-indigo-600 dark:text-indigo-400',
        'violet' => 'text-violet-600 dark:text-violet-400',
        'purple' => 'text-purple-600 dark:text-purple-400',
        'fuchsia' => 'text-fuchsia-600 dark:text-fuchsia-400',
        'pink' => 'text-pink-600 dark:text-pink-400',
        'rose' => 'text-rose-600 dark:text-rose-400',
    } : match ($variant) {
        'strong' => '[:where(&)]:text-zinc-800 [:where(&)]:dark:text-white',
        'subtle' => '[:where(&)]:text-zinc-400 [:where(&)]:dark:text-white/50',
        default => '[:where(&)]:text-zinc-500 [:where(&)]:dark:text-white/70',
    })
    ;
?>

<?php if ($inline) : ?><span <?php echo e($attributes->class($classes)); ?> data-flux-text <?php if($color): ?> color="<?php echo e($color); ?>" <?php endif; ?>><?php echo e($slot); ?></span><?php else: ?><p <?php echo e($attributes->class($classes)); ?> data-flux-text <?php if($color): ?> data-color="<?php echo e($color); ?>" <?php endif; ?>><?php echo e($slot); ?></p><?php endif; ?><?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\text.blade.php ENDPATH**/ ?>