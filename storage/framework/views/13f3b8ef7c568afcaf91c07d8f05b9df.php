<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->hasPages()): ?>
    <nav>
        <ul class="pagination">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->onFirstPage()): ?>
                <li class="disabled" aria-disabled="true"><span><?php echo app('translator')->get('pagination.previous'); ?></span></li>
            <?php else: ?>
                <li><a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev"><?php echo app('translator')->get('pagination.previous'); ?></a></li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->hasMorePages()): ?>
                <li><a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next"><?php echo app('translator')->get('pagination.next'); ?></a></li>
            <?php else: ?>
                <li class="disabled" aria-disabled="true"><span><?php echo app('translator')->get('pagination.next'); ?></span></li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ul>
    </nav>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\laravel\framework\src\Illuminate\Pagination\resources\views\simple-default.blade.php ENDPATH**/ ?>