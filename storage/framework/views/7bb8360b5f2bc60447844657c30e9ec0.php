

<?php $tooltipPosition = $tooltipPosition ??= $attributes->pluck('tooltip:position'); ?>
<?php $tooltipKbd = $tooltipKbd ??= $attributes->pluck('tooltip:kbd'); ?>
<?php $tooltip = $tooltip ??= $attributes->pluck('tooltip'); ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'tooltipPosition' => 'right',
    'placeholder' => __('Search...'),
    'tooltipKbd' => null,
    'tooltip' => null,
    'kbd' => null,
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
    'tooltipPosition' => 'right',
    'placeholder' => __('Search...'),
    'tooltipKbd' => null,
    'tooltip' => null,
    'kbd' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$tooltip = $tooltip ?? $placeholder;

$tooltipKbd ??= $kbd;

$tooltipClasses = Flux::classes()
    ->add('w-full')
    ->add('in-data-flux-sidebar-header:in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:hidden')
    ;

$classes = Flux::classes()
    ->add('h-10 py-2 px-3 w-full rounded-lg disabled:shadow-none dark:shadow-none appearance-none text-base sm:text-sm leading-[1.375rem] bg-zinc-800/5 dark:bg-white/10 dark:disabled:bg-white/[7%] text-zinc-700 placeholder-zinc-500 disabled:placeholder-zinc-400 dark:text-zinc-200 dark:placeholder-white/60 dark:disabled:placeholder-white/40 border-0 relative flex items-center gap-3')
    ->add('in-data-flux-sidebar-on-mobile:h-10 in-data-flux-sidebar-collapsed-desktop:px-3')
    ->add('in-data-flux-sidebar-header:in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:hidden')
    ;
?>

<?php if (isset($component)) { $__componentOriginalf5109f209df079b3a83484e1e6310749 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5109f209df079b3a83484e1e6310749 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::tooltip.index','data' => ['position' => $tooltipPosition,'class' => $tooltipClasses]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['position' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tooltipPosition),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tooltipClasses)]); ?>
    <button
        <?php echo e($attributes->class($classes)); ?>

        type="button"
        data-flux-sidebar-search
    >
        <div class="flex items-center justify-center text-xs text-zinc-400/75 start-0">
            <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['class' => 'size-4','icon' => 'magnifying-glass','variant' => 'outline']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4','icon' => 'magnifying-glass','variant' => 'outline']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $attributes = $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2)): ?>
<?php $component = $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2; ?>
<?php unset($__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2); ?>
<?php endif; ?>
        </div>

        <div class="in-data-flux-sidebar-collapsed-desktop:hidden block self-center text-start flex-1 font-medium text-zinc-400 dark:text-white/40">
            <?php echo e($placeholder); ?>

        </div>

        <?php if ($kbd): ?>
            <div class="in-data-flux-sidebar-collapsed-desktop:hidden absolute top-0 bottom-0 flex items-center justify-center text-xs text-zinc-400/75 pe-4 end-0">
                <?php echo e($kbd); ?>

            </div>
        <?php endif; ?>
    </button>

    <?php if (isset($component)) { $__componentOriginalb33170285c50607a3d87e01ed45caae5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb33170285c50607a3d87e01ed45caae5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::tooltip.content','data' => ['kbd' => $tooltipKbd,'class' => 'not-in-data-flux-sidebar-collapsed-desktop:hidden cursor-default']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::tooltip.content'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kbd' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tooltipKbd),'class' => 'not-in-data-flux-sidebar-collapsed-desktop:hidden cursor-default']); ?>
        <?php echo e($tooltip); ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb33170285c50607a3d87e01ed45caae5)): ?>
<?php $attributes = $__attributesOriginalb33170285c50607a3d87e01ed45caae5; ?>
<?php unset($__attributesOriginalb33170285c50607a3d87e01ed45caae5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb33170285c50607a3d87e01ed45caae5)): ?>
<?php $component = $__componentOriginalb33170285c50607a3d87e01ed45caae5; ?>
<?php unset($__componentOriginalb33170285c50607a3d87e01ed45caae5); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf5109f209df079b3a83484e1e6310749)): ?>
<?php $attributes = $__attributesOriginalf5109f209df079b3a83484e1e6310749; ?>
<?php unset($__attributesOriginalf5109f209df079b3a83484e1e6310749); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf5109f209df079b3a83484e1e6310749)): ?>
<?php $component = $__componentOriginalf5109f209df079b3a83484e1e6310749; ?>
<?php unset($__componentOriginalf5109f209df079b3a83484e1e6310749); ?>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\sidebar\search.blade.php ENDPATH**/ ?>