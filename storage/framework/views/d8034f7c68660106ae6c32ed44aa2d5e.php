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
        <nav>
            <ul class="pagination">
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->onFirstPage()): ?>
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link"><?php echo app('translator')->get('pagination.previous'); ?></span>
                    </li>
                <?php else: ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(method_exists($paginator,'getCursorName')): ?>
                        <li class="page-item">
                            <button dusk="previousPage" type="button" class="page-link" wire:key="cursor-<?php echo e($paginator->getCursorName()); ?>-<?php echo e($paginator->previousCursor()->encode()); ?>" wire:click="setPage('<?php echo e($paginator->previousCursor()->encode()); ?>','<?php echo e($paginator->getCursorName()); ?>')" x-on:click="<?php echo e($scrollIntoViewJsSnippet); ?>" wire:loading.attr="disabled"><?php echo app('translator')->get('pagination.previous'); ?></button>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <button type="button" dusk="previousPage<?php echo e($paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName()); ?>" class="page-link" wire:click="previousPage('<?php echo e($paginator->getPageName()); ?>')" x-on:click="<?php echo e($scrollIntoViewJsSnippet); ?>" wire:loading.attr="disabled"><?php echo app('translator')->get('pagination.previous'); ?></button>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->hasMorePages()): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(method_exists($paginator,'getCursorName')): ?>
                        <li class="page-item">
                            <button dusk="nextPage" type="button" class="page-link" wire:key="cursor-<?php echo e($paginator->getCursorName()); ?>-<?php echo e($paginator->nextCursor()->encode()); ?>" wire:click="setPage('<?php echo e($paginator->nextCursor()->encode()); ?>','<?php echo e($paginator->getCursorName()); ?>')" x-on:click="<?php echo e($scrollIntoViewJsSnippet); ?>" wire:loading.attr="disabled"><?php echo app('translator')->get('pagination.next'); ?></button>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <button type="button" dusk="nextPage<?php echo e($paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName()); ?>" class="page-link" wire:click="nextPage('<?php echo e($paginator->getPageName()); ?>')" x-on:click="<?php echo e($scrollIntoViewJsSnippet); ?>" wire:loading.attr="disabled"><?php echo app('translator')->get('pagination.next'); ?></button>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link"><?php echo app('translator')->get('pagination.next'); ?></span>
                    </li>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\livewire\src\Features\SupportPagination\views\simple-bootstrap.blade.php ENDPATH**/ ?>