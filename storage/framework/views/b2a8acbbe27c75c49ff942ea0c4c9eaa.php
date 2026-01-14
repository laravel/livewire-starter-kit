<?php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
?>

<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->hasPages()): ?>
        <nav class="d-flex justify-items-center justify-content-between">
            <div class="d-flex justify-content-between flex-fill d-sm-none">
                <ul class="pagination">
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->onFirstPage()): ?>
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link"><?php echo app('translator')->get('pagination.previous'); ?></span>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <button type="button" dusk="previousPage<?php echo e($paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName()); ?>" class="page-link" wire:click="previousPage('<?php echo e($paginator->getPageName()); ?>')" x-on:click="<?php echo e($scrollIntoViewJsSnippet); ?>" wire:loading.attr="disabled"><?php echo app('translator')->get('pagination.previous'); ?></button>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->hasMorePages()): ?>
                        <li class="page-item">
                            <button type="button" dusk="nextPage<?php echo e($paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName()); ?>" class="page-link" wire:click="nextPage('<?php echo e($paginator->getPageName()); ?>')" x-on:click="<?php echo e($scrollIntoViewJsSnippet); ?>" wire:loading.attr="disabled"><?php echo app('translator')->get('pagination.next'); ?></button>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link" aria-hidden="true"><?php echo app('translator')->get('pagination.next'); ?></span>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>

            <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between">
                <div>
                    <p class="small text-muted">
                        <?php echo __('Showing'); ?>

                        <span class="fw-semibold"><?php echo e($paginator->firstItem()); ?></span>
                        <?php echo __('to'); ?>

                        <span class="fw-semibold"><?php echo e($paginator->lastItem()); ?></span>
                        <?php echo __('of'); ?>

                        <span class="fw-semibold"><?php echo e($paginator->total()); ?></span>
                        <?php echo __('results'); ?>

                    </p>
                </div>

                <div>
                    <ul class="pagination">
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->onFirstPage()): ?>
                            <li class="page-item disabled" aria-disabled="true" aria-label="<?php echo app('translator')->get('pagination.previous'); ?>">
                                <span class="page-link" aria-hidden="true">&lsaquo;</span>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <button type="button" dusk="previousPage<?php echo e($paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName()); ?>" class="page-link" wire:click="previousPage('<?php echo e($paginator->getPageName()); ?>')" x-on:click="<?php echo e($scrollIntoViewJsSnippet); ?>" wire:loading.attr="disabled" aria-label="<?php echo app('translator')->get('pagination.previous'); ?>">&lsaquo;</button>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_string($element)): ?>
                                <li class="page-item disabled" aria-disabled="true"><span class="page-link"><?php echo e($element); ?></span></li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($element)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($page == $paginator->currentPage()): ?>
                                        <li class="page-item active" wire:key="paginator-<?php echo e($paginator->getPageName()); ?>-page-<?php echo e($page); ?>" aria-current="page"><span class="page-link"><?php echo e($page); ?></span></li>
                                    <?php else: ?>
                                        <li class="page-item" wire:key="paginator-<?php echo e($paginator->getPageName()); ?>-page-<?php echo e($page); ?>"><button type="button" class="page-link" wire:click="gotoPage(<?php echo e($page); ?>, '<?php echo e($paginator->getPageName()); ?>')" x-on:click="<?php echo e($scrollIntoViewJsSnippet); ?>"><?php echo e($page); ?></button></li>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->hasMorePages()): ?>
                            <li class="page-item">
                                <button type="button" dusk="nextPage<?php echo e($paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName()); ?>" class="page-link" wire:click="nextPage('<?php echo e($paginator->getPageName()); ?>')" x-on:click="<?php echo e($scrollIntoViewJsSnippet); ?>" wire:loading.attr="disabled" aria-label="<?php echo app('translator')->get('pagination.next'); ?>">&rsaquo;</button>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled" aria-disabled="true" aria-label="<?php echo app('translator')->get('pagination.next'); ?>">
                                <span class="page-link" aria-hidden="true">&rsaquo;</span>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\livewire\src\Features\SupportPagination\views\bootstrap.blade.php ENDPATH**/ ?>