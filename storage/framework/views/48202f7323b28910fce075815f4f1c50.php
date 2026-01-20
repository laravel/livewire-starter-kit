

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'size' => 'base',
    'accent' => false,
    'level' => null,
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
    'size' => 'base',
    'accent' => false,
    'level' => null,
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
    ->add('font-medium')
    ->add(match ($accent) {
        true => 'text-[var(--color-accent-content)]',
        default => '[:where(&)]:text-zinc-800 [:where(&)]:dark:text-white',
    })
    ->add(match ($size) {
        'xl' => 'text-2xl [&:has(+[data-flux-subheading])]:mb-2 [[data-flux-subheading]+&]:mt-2',
        'lg' => 'text-base [&:has(+[data-flux-subheading])]:mb-2 [[data-flux-subheading]+&]:mt-2',
        default => 'text-sm [&:has(+[data-flux-subheading])]:mb-2 [[data-flux-subheading]+&]:mt-2',
    })
    ;
?>

<?php switch ((int) $level): case(1): ?>
        <h1 <?php echo e($attributes->class($classes)); ?> data-flux-heading><?php echo e($slot); ?></h1>

        <?php break; ?>
    <?php case(2): ?>
        <h2 <?php echo e($attributes->class($classes)); ?> data-flux-heading><?php echo e($slot); ?></h2>

        <?php break; ?>
    <?php case(3): ?>
        <h3 <?php echo e($attributes->class($classes)); ?> data-flux-heading><?php echo e($slot); ?></h3>

        <?php break; ?>
    <?php case(4): ?>
        <h4 <?php echo e($attributes->class($classes)); ?> data-flux-heading><?php echo e($slot); ?></h4>

        <?php break; ?>
    <?php default: ?>
        <div <?php echo e($attributes->class($classes)); ?> data-flux-heading><?php echo e($slot); ?></div>
<?php endswitch; ?>
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\vendor\livewire\flux\src/../stubs/resources/views/flux/heading.blade.php ENDPATH**/ ?>