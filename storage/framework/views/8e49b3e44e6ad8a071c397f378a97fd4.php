



<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'outline',
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
    'variant' => 'outline',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$classes = Flux::classes('shrink-0')
    ->add(match($variant) {
        'outline' => '[:where(&)]:size-6',
        'solid' => '[:where(&)]:size-6',
        'mini' => '[:where(&)]:size-5',
        'micro' => '[:where(&)]:size-4',
    });
?>

<?php switch ($variant): case ('outline'): ?>
<svg <?php echo e($attributes->class($classes)); ?> data-flux-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5V18M15 7.5V18M3 16.811V8.69c0-.864.933-1.406 1.683-.977l7.108 4.061a1.125 1.125 0 0 1 0 1.954l-7.108 4.061A1.125 1.125 0 0 1 3 16.811Z"/>
</svg>

        <?php break; ?>

    <?php case ('solid'): ?>
<svg <?php echo e($attributes->class($classes)); ?> data-flux-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
  <path d="M15 6.75a.75.75 0 0 0-.75.75V18a.75.75 0 0 0 .75.75h.75a.75.75 0 0 0 .75-.75V7.5a.75.75 0 0 0-.75-.75H15ZM20.25 6.75a.75.75 0 0 0-.75.75V18c0 .414.336.75.75.75H21a.75.75 0 0 0 .75-.75V7.5a.75.75 0 0 0-.75-.75h-.75ZM5.055 7.06C3.805 6.347 2.25 7.25 2.25 8.69v8.122c0 1.44 1.555 2.343 2.805 1.628l7.108-4.061c1.26-.72 1.26-2.536 0-3.256L5.055 7.061Z"/>
</svg>

        <?php break; ?>

    <?php case ('mini'): ?>
<svg <?php echo e($attributes->class($classes)); ?> data-flux-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
  <path d="M12.75 4a.75.75 0 0 0-.75.75v10.5c0 .414.336.75.75.75h.5a.75.75 0 0 0 .75-.75V4.75a.75.75 0 0 0-.75-.75h-.5ZM17.75 4a.75.75 0 0 0-.75.75v10.5c0 .414.336.75.75.75h.5a.75.75 0 0 0 .75-.75V4.75a.75.75 0 0 0-.75-.75h-.5ZM3.288 4.819A1.5 1.5 0 0 0 1 6.095v7.81a1.5 1.5 0 0 0 2.288 1.277l6.323-3.906a1.5 1.5 0 0 0 0-2.552L3.288 4.819Z"/>
</svg>

        <?php break; ?>

    <?php case ('micro'): ?>
<svg <?php echo e($attributes->class($classes)); ?> data-flux-icon xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
  <path d="M1 4.804a1 1 0 0 1 1.53-.848l5.113 3.196a1 1 0 0 1 0 1.696L2.53 12.044A1 1 0 0 1 1 11.196V4.804ZM13.5 4.5A.5.5 0 0 1 14 4h.5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5H14a.5.5 0 0 1-.5-.5v-7ZM10.5 4a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .5.5h.5a.5.5 0 0 0 .5-.5v-7A.5.5 0 0 0 11 4h-.5Z"/>
</svg>

        <?php break; ?>

<?php endswitch; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\icon\play-pause.blade.php ENDPATH**/ ?>