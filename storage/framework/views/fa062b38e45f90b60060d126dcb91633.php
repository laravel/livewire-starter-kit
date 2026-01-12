

<?php
$classes = Flux::classes()
    ->add('z-20 fixed inset-0 bg-black/10 hidden')
    ->add('data-flux-sidebar-on-mobile:not-data-flux-sidebar-collapsed-mobile:block')
    ;
?>

<ui-sidebar-toggle <?php echo e($attributes->class($classes)); ?> data-flux-sidebar-backdrop></ui-sidebar-toggle>
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\vendor\livewire\flux\src/../stubs/resources/views/flux/sidebar/backdrop.blade.php ENDPATH**/ ?>