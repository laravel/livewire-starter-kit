<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'expandable' => false,
    'expanded' => true,
    'heading' => null,
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
    'expandable' => false,
    'expanded' => true,
    'heading' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if ($expandable && $heading): ?>

<ui-disclosure
    <?php echo e($attributes->class('group/disclosure')); ?>

    <?php if($expanded === true): ?> open <?php endif; ?>
    data-flux-navlist-group
>
    <button
        type="button"
        class="group/disclosure-button mb-[2px] flex h-10 w-full items-center rounded-lg text-zinc-500 hover:bg-zinc-800/5 hover:text-zinc-800 lg:h-8 dark:text-white/80 dark:hover:bg-white/[7%] dark:hover:text-white"
    >
        <div class="ps-3 pe-4">
            <?php if (isset($component)) { $__componentOriginal298ff21bbc41cebb188cbb18c6c11bc0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal298ff21bbc41cebb188cbb18c6c11bc0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.chevron-down','data' => ['class' => 'hidden size-3! group-data-open/disclosure-button:block']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.chevron-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'hidden size-3! group-data-open/disclosure-button:block']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal298ff21bbc41cebb188cbb18c6c11bc0)): ?>
<?php $attributes = $__attributesOriginal298ff21bbc41cebb188cbb18c6c11bc0; ?>
<?php unset($__attributesOriginal298ff21bbc41cebb188cbb18c6c11bc0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal298ff21bbc41cebb188cbb18c6c11bc0)): ?>
<?php $component = $__componentOriginal298ff21bbc41cebb188cbb18c6c11bc0; ?>
<?php unset($__componentOriginal298ff21bbc41cebb188cbb18c6c11bc0); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal31cb76c8d087d4f00797aeea7232b4c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31cb76c8d087d4f00797aeea7232b4c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.chevron-right','data' => ['class' => 'block size-3! group-data-open/disclosure-button:hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.chevron-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'block size-3! group-data-open/disclosure-button:hidden']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31cb76c8d087d4f00797aeea7232b4c3)): ?>
<?php $attributes = $__attributesOriginal31cb76c8d087d4f00797aeea7232b4c3; ?>
<?php unset($__attributesOriginal31cb76c8d087d4f00797aeea7232b4c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31cb76c8d087d4f00797aeea7232b4c3)): ?>
<?php $component = $__componentOriginal31cb76c8d087d4f00797aeea7232b4c3; ?>
<?php unset($__componentOriginal31cb76c8d087d4f00797aeea7232b4c3); ?>
<?php endif; ?>
        </div>

        <span class="text-sm font-medium leading-none"><?php echo e($heading); ?></span>
    </button>

    <div class="relative hidden space-y-[2px] ps-7 data-open:block" <?php if($expanded === true): ?> data-open <?php endif; ?>>
        <div class="absolute inset-y-[3px] start-0 ms-4 w-px bg-zinc-200 dark:bg-white/30"></div>

        <?php echo e($slot); ?>

    </div>
</ui-disclosure>

<?php elseif ($heading): ?>

<div <?php echo e($attributes->class('block space-y-[2px]')); ?>>
    <div class="px-1 py-2">
        <div class="text-xs leading-none text-zinc-400"><?php echo e($heading); ?></div>
    </div>

    <div>
        <?php echo e($slot); ?>

    </div>
</div>

<?php else: ?>

<div <?php echo e($attributes->class('block space-y-[2px]')); ?>>
    <?php echo e($slot); ?>

</div>

<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\resources\views\flux\navlist\group.blade.php ENDPATH**/ ?>