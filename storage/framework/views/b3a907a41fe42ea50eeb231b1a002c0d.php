

<?php
$classes = Flux::classes([
    'p-2 pb-1 w-full',
    'flex items-center',
    'text-start text-xs font-medium',
    'text-zinc-500 font-medium dark:text-zinc-300',
]);
?>

<div <?php echo e($attributes->class($classes)); ?> data-flux-menu-heading>
    <div class="w-7 hidden [[data-flux-menu]:has(>[data-flux-menu-item-has-icon])_&]:block"></div>

    <div><?php echo e($slot); ?></div>
</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\menu\heading.blade.php ENDPATH**/ ?>