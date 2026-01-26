

<?php
$classes = Flux::classes()
    ->add('[:where(&)]:min-w-48 p-[.3125rem]')
    ->add('rounded-lg shadow-xs')
    ->add('border border-zinc-200 dark:border-zinc-600')
    ->add('bg-white dark:bg-zinc-700')
    ->add('focus:outline-hidden')
    ;
?>

<ui-menu
    <?php echo e($attributes->class($classes)); ?>

    popover="manual"
    data-flux-menu
>
    <?php echo e($slot); ?>

</ui-menu>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\src/../stubs/resources/views/flux/menu/index.blade.php ENDPATH**/ ?>