

<?php $logoDark ??= $attributes->pluck('logo:dark'); ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,
    'logo' => null,
    'logoDark' => null,
    'alt' => null,
    'href' => '/',
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
    'name' => null,
    'logo' => null,
    'logoDark' => null,
    'alt' => null,
    'href' => '/',
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
    ->add('h-10 flex items-center px-2 in-data-flux-sidebar-collapsed-desktop:w-10 in-data-flux-sidebar-collapsed-desktop:px-2')
    ->add('in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:absolute')
    ->add('in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:opacity-0')
    ;

$textClasses = Flux::classes()
    ->add('text-sm font-medium truncate [:where(&)]:text-zinc-800 dark:[:where(&)]:text-zinc-100')
    ;
?>

<?php if ($name): ?>
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->class([ $classes, 'gap-2' ])); ?> data-flux-sidebar-brand>
        <?php if ($logo instanceof \Illuminate\View\ComponentSlot): ?>
            <div <?php echo e($logo->attributes->class('flex items-center justify-center [:where(&)]:h-6 [:where(&)]:min-w-6 [:where(&)]:rounded-sm overflow-hidden shrink-0')); ?>>
                <?php echo e($logo); ?>

            </div>
        <?php else: ?>
            <div class="flex items-center justify-center h-6 min-w-6 rounded-sm overflow-hidden shrink-0">
                <?php if ($logoDark): ?>
                    <img src="<?php echo e($logo); ?>" alt="<?php echo e($alt); ?>" class="h-6 min-w-6 dark:hidden" />
                    <img src="<?php echo e($logoDark); ?>" alt="<?php echo e($alt); ?>" class="h-6 min-w-6 hidden dark:block" />
                <?php elseif ($logo): ?>
                    <img src="<?php echo e($logo); ?>" alt="<?php echo e($alt); ?>" class="h-6 min-w-6" />
                <?php else: ?>
                    <?php echo e($slot); ?>

                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="<?php echo e($textClasses); ?> in-data-flux-sidebar-collapsed-desktop:hidden"><?php echo e($name); ?></div>
    </a>
<?php else: ?>
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->class($classes)); ?> data-flux-sidebar-brand>
        <?php if ($logo instanceof \Illuminate\View\ComponentSlot): ?>
            <div <?php echo e($logo->attributes->class('flex items-center justify-center [:where(&)]:h-6 [:where(&)]:min-w-6 [:where(&)]:rounded-sm overflow-hidden shrink-0')); ?>>
                <?php echo e($logo); ?>

            </div>
        <?php else: ?>
            <div class="flex items-center justify-center h-6 rounded-sm overflow-hidden shrink-0">
                <?php if ($logoDark): ?>
                    <img src="<?php echo e($logo); ?>" alt="<?php echo e($alt); ?>" class="h-6 dark:hidden" />
                    <img src="<?php echo e($logoDark); ?>" alt="<?php echo e($alt); ?>" class="h-6 hidden dark:block" />
                <?php elseif ($logo): ?>
                    <img src="<?php echo e($logo); ?>" alt="<?php echo e($alt); ?>" class="h-6" />
                <?php else: ?>
                    <?php echo e($slot); ?>

                <?php endif; ?>
            </div>
        <?php endif; ?>
    </a>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\sidebar\brand.blade.php ENDPATH**/ ?>