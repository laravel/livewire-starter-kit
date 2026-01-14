

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'placeholder' => null,
    'invalid' => null,
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
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'placeholder' => null,
    'invalid' => null,
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
$invalid ??= ($name && $errors->has($name));

$classes = Flux::classes()
    ->add('appearance-none') // Strip the browser's default <select> styles...
    ->add('[:where(&)]:w-full ps-3 pe-10 block')
    ->add(match ($size) {
        default => 'h-10 py-2 text-base sm:text-sm leading-[1.375rem] rounded-lg',
        'sm' => 'h-8 py-1.5 text-sm leading-[1.125rem] rounded-md',
        'xs' => 'h-6 text-xs leading-[1.125rem] rounded-md',
    })
    ->add('shadow-xs border')
    ->add('bg-white dark:bg-white/10 dark:disabled:bg-white/[7%]')
    ->add('text-zinc-700 dark:text-zinc-300 disabled:text-zinc-500 dark:disabled:text-zinc-400')
    // Make the placeholder match the text color of standard input placeholders...
    ->add('has-[option.placeholder:checked]:text-zinc-400 dark:has-[option.placeholder:checked]:text-zinc-400')
    // Options on Windows don't inherit dark mode styles, so we need to force them...
    ->add('dark:[&>option]:bg-zinc-700 dark:[&>option]:text-white')
    ->add('disabled:shadow-none')
    ->add($invalid
        ? 'border border-red-500'
        : 'border border-zinc-200 border-b-zinc-300/80 dark:border-white/10'
    )
    ;
?>

<select
    <?php echo e($attributes->class($classes)); ?>

    <?php if($invalid): ?> aria-invalid="true" data-invalid <?php endif; ?>
    <?php if(isset($name)): ?> name="<?php echo e($name); ?>" <?php endif; ?>
    <?php if(is_numeric($size)): ?> size="<?php echo e($size); ?>" <?php endif; ?>
    data-flux-control
    data-flux-select-native
    data-flux-group-target
>
    <?php if ($placeholder): ?>
        <option value="" disabled selected class="placeholder"><?php echo e($placeholder); ?></option>
    <?php endif; ?>

    <?php echo e($slot); ?>

</select>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\select\variants\default.blade.php ENDPATH**/ ?>