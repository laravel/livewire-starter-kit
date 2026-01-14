

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'shortcut' => null,
    'name' => null,
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
    'shortcut' => null,
    'name' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    <?php echo e($attributes->class('contents')); ?>

    x-data
    x-on:click="$el.querySelector('button[disabled]') || $dispatch('modal-show', { name: '<?php echo e($name); ?>' })"
    <?php if($shortcut): ?>
        x-on:keydown.<?php echo e($shortcut); ?>.document="$event.preventDefault(); $dispatch('modal-show', { name: '<?php echo e($name); ?>' })"
    <?php endif; ?>
    data-flux-modal-trigger
>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\modal\trigger.blade.php ENDPATH**/ ?>