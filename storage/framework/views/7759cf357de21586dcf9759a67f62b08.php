

<?php $iconVariant ??= $attributes->pluck('icon:variant'); ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'iconVariant' => 'mini',
    'controls' => null,
    'heading' => null,
    'color' => 'white',
    'variant' => null,
    'actions' => null,
    'content' => null,
    'inline' => null,
    'text' => null,
    'icon' => null,
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
    'iconVariant' => 'mini',
    'controls' => null,
    'heading' => null,
    'color' => 'white',
    'variant' => null,
    'actions' => null,
    'content' => null,
    'inline' => null,
    'text' => null,
    'icon' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    if ($color === 'gray') $color = 'zinc';

    $color = match($variant) {
        'success' => 'green',
        'danger' => 'red',
        'warning' => 'yellow',
        'secondary' => 'zinc',
        default => $color,
    };

    $classes = Flux::classes()
        ->add('@container p-2 flex border rounded-xl')
        ->add([
            'border-(--callout-border) bg-(--callout-background)',
            '[&_[data-slot=heading]]:text-(--callout-heading)',
            '[&_[data-slot=text]]:text-(--callout-text)',
        ])
        ->add(match($color) {
            'blue' => [
                '[--callout-border:var(--color-blue-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-blue-400),transparent_50%)]',
                '[--callout-background:var(--color-blue-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-blue-400),transparent_90%)]',
                '[--callout-heading:var(--color-blue-600)] dark:[--callout-heading:var(--color-blue-200)]',
                '[--callout-text:var(--color-blue-600)] dark:[--callout-text:var(--color-blue-300)]',
                '[--callout-icon:var(--color-blue-500)] dark:[--callout-icon:var(--color-blue-400)]',
            ],
            'sky' => [
                '[--callout-border:var(--color-sky-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-sky-400),transparent_50%)]',
                '[--callout-background:var(--color-sky-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-sky-400),transparent_90%)]',
                '[--callout-heading:var(--color-sky-600)] dark:[--callout-heading:var(--color-sky-200)]',
                '[--callout-text:var(--color-sky-600)] dark:[--callout-text:var(--color-sky-300)]',
                '[--callout-icon:var(--color-sky-500)] dark:[--callout-icon:var(--color-sky-400)]',
            ],
            'red' => [
                '[--callout-border:var(--color-red-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-red-400),transparent_50%)]',
                '[--callout-background:var(--color-red-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-red-400),transparent_90%)]',
                '[--callout-heading:var(--color-red-700)] dark:[--callout-heading:var(--color-red-200)]',
                '[--callout-text:var(--color-red-700)] dark:[--callout-text:var(--color-red-300)]',
                '[--callout-icon:var(--color-red-400)] dark:[--callout-icon:var(--color-red-400)]',
            ],
            'orange' => [
                '[--callout-border:var(--color-orange-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-orange-400),transparent_50%)]',
                '[--callout-background:var(--color-orange-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-orange-400),transparent_90%)]',
                '[--callout-heading:var(--color-orange-600)] dark:[--callout-heading:var(--color-orange-200)]',
                '[--callout-text:var(--color-orange-600)] dark:[--callout-text:var(--color-orange-300)]',
                '[--callout-icon:var(--color-orange-500)] dark:[--callout-icon:var(--color-orange-400)]',
            ],
            'amber' => [
                '[--callout-border:var(--color-amber-400)] dark:[--callout-border:color-mix(in_oklab,var(--color-amber-400),transparent_50%)]',
                '[--callout-background:var(--color-amber-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-amber-400),transparent_90%)]',
                '[--callout-heading:var(--color-amber-600)] dark:[--callout-heading:var(--color-amber-200)]',
                '[--callout-text:var(--color-amber-600)] dark:[--callout-text:var(--color-amber-300)]',
                '[--callout-icon:var(--color-amber-500)] dark:[--callout-icon:var(--color-amber-400)]',
            ],
            'yellow' => [
                '[--callout-border:var(--color-yellow-400)] dark:[--callout-border:color-mix(in_oklab,var(--color-yellow-400),transparent_50%)]',
                '[--callout-background:var(--color-yellow-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-yellow-400),transparent_90%)]',
                '[--callout-heading:var(--color-yellow-600)] dark:[--callout-heading:var(--color-yellow-200)]',
                '[--callout-text:var(--color-yellow-700)] dark:[--callout-text:var(--color-yellow-300)]',
                '[--callout-icon:var(--color-yellow-500)] dark:[--callout-icon:var(--color-yellow-400)]',
            ],
            'lime' => [
                '[--callout-border:var(--color-lime-400)] dark:[--callout-border:color-mix(in_oklab,var(--color-lime-400),transparent_50%)]',
                '[--callout-background:var(--color-lime-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-lime-400),transparent_90%)]',
                '[--callout-heading:var(--color-lime-700)] dark:[--callout-heading:var(--color-lime-200)]',
                '[--callout-text:var(--color-lime-600)] dark:[--callout-text:var(--color-lime-300)]',
                '[--callout-icon:var(--color-lime-500)] dark:[--callout-icon:var(--color-lime-400)]',
            ],
            'green' => [
                '[--callout-border:var(--color-green-300)] dark:[--callout-border:color-mix(in_oklab,var(--color-green-400),transparent_50%)]',
                '[--callout-background:var(--color-green-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-green-400),transparent_90%)]',
                '[--callout-heading:var(--color-green-600)] dark:[--callout-heading:var(--color-green-200)]',
                '[--callout-text:var(--color-green-600)] dark:[--callout-text:var(--color-green-300)]',
                '[--callout-icon:var(--color-green-500)] dark:[--callout-icon:var(--color-green-400)]',
            ],
            'emerald' => [
                '[--callout-border:var(--color-emerald-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-emerald-400),transparent_50%)]',
                '[--callout-background:var(--color-emerald-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-emerald-400),transparent_90%)]',
                '[--callout-heading:var(--color-emerald-600)] dark:[--callout-heading:var(--color-emerald-200)]',
                '[--callout-text:var(--color-emerald-600)] dark:[--callout-text:var(--color-emerald-300)]',
                '[--callout-icon:var(--color-emerald-500)] dark:[--callout-icon:var(--color-emerald-400)]',
            ],
            'teal' => [
                '[--callout-border:var(--color-teal-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-teal-400),transparent_50%)]',
                '[--callout-background:var(--color-teal-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-teal-400),transparent_90%)]',
                '[--callout-heading:var(--color-teal-600)] dark:[--callout-heading:var(--color-teal-200)]',
                '[--callout-text:var(--color-teal-600)] dark:[--callout-text:var(--color-teal-300)]',
                '[--callout-icon:var(--color-teal-500)] dark:[--callout-icon:var(--color-teal-400)]',
            ],
            'cyan' => [
                '[--callout-border:var(--color-cyan-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-cyan-400),transparent_50%)]',
                '[--callout-background:var(--color-cyan-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-cyan-400),transparent_90%)]',
                '[--callout-heading:var(--color-cyan-600)] dark:[--callout-heading:var(--color-cyan-200)]',
                '[--callout-text:var(--color-cyan-600)] dark:[--callout-text:var(--color-cyan-300)]',
                '[--callout-icon:var(--color-cyan-500)] dark:[--callout-icon:var(--color-cyan-400)]',
            ],
            'indigo' => [
                '[--callout-border:var(--color-indigo-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-indigo-400),transparent_50%)]',
                '[--callout-background:var(--color-indigo-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-indigo-400),transparent_90%)]',
                '[--callout-heading:var(--color-indigo-600)] dark:[--callout-heading:var(--color-indigo-200)]',
                '[--callout-text:var(--color-indigo-600)] dark:[--callout-text:var(--color-indigo-300)]',
                '[--callout-icon:var(--color-indigo-500)] dark:[--callout-icon:var(--color-indigo-400)]',
            ],
            'violet' => [
                '[--callout-border:var(--color-violet-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-violet-400),transparent_50%)]',
                '[--callout-background:var(--color-violet-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-violet-400),transparent_90%)]',
                '[--callout-heading:var(--color-violet-600)] dark:[--callout-heading:var(--color-violet-200)]',
                '[--callout-text:var(--color-violet-600)] dark:[--callout-text:var(--color-violet-300)]',
                '[--callout-icon:var(--color-violet-500)] dark:[--callout-icon:var(--color-violet-400)]',
            ],
            'purple' => [
                '[--callout-border:var(--color-purple-300)] dark:[--callout-border:color-mix(in_oklab,var(--color-purple-400),transparent_50%)]',
                '[--callout-background:var(--color-purple-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-purple-400),transparent_90%)]',
                '[--callout-heading:var(--color-purple-800)] dark:[--callout-heading:var(--color-purple-200)]',
                '[--callout-text:var(--color-purple-700)] dark:[--callout-text:var(--color-purple-300)]',
                '[--callout-icon:var(--color-purple-500)] dark:[--callout-icon:var(--color-purple-400)]',
            ],
            'fuchsia' => [
                '[--callout-border:var(--color-fuchsia-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-fuchsia-400),transparent_50%)]',
                '[--callout-background:var(--color-fuchsia-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-fuchsia-400),transparent_90%)]',
                '[--callout-heading:var(--color-fuchsia-600)] dark:[--callout-heading:var(--color-fuchsia-200)]',
                '[--callout-text:var(--color-fuchsia-600)] dark:[--callout-text:var(--color-fuchsia-300)]',
                '[--callout-icon:var(--color-fuchsia-500)] dark:[--callout-icon:var(--color-fuchsia-400)]',
            ],
            'pink' => [
                '[--callout-border:var(--color-pink-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-pink-400),transparent_50%)]',
                '[--callout-background:var(--color-pink-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-pink-400),transparent_90%)]',
                '[--callout-heading:var(--color-pink-600)] dark:[--callout-heading:var(--color-pink-200)]',
                '[--callout-text:var(--color-pink-600)] dark:[--callout-text:var(--color-pink-300)]',
                '[--callout-icon:var(--color-pink-500)] dark:[--callout-icon:var(--color-pink-400)]',
            ],
            'rose' => [
                '[--callout-border:var(--color-rose-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-rose-400),transparent_50%)]',
                '[--callout-background:var(--color-rose-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-rose-400),transparent_90%)]',
                '[--callout-heading:var(--color-rose-600)] dark:[--callout-heading:var(--color-rose-200)]',
                '[--callout-text:var(--color-rose-600)] dark:[--callout-text:var(--color-rose-300)]',
                '[--callout-icon:var(--color-rose-500)] dark:[--callout-icon:var(--color-rose-400)]',
            ],
            'zinc' => [
                '[--callout-border:var(--color-zinc-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-white),transparent_95%)]',
                '[--callout-background:var(--color-zinc-50)] dark:[--callout-background:color-mix(in_oklab,var(--color-zinc-400),transparent_90%)]',
                '[--callout-heading:var(--color-zinc-800)] dark:[--callout-heading:var(--color-zinc-200)]',
                '[--callout-text:var(--color-zinc-500)] dark:[--callout-text:var(--color-zinc-300)]',
                '[--callout-icon:var(--color-zinc-400)] dark:[--callout-icon:var(--color-zinc-400)]',
            ],
            default => [
                '[--callout-border:var(--color-zinc-200)] dark:[--callout-border:color-mix(in_oklab,var(--color-white),transparent_95%)]',
                '[--callout-background:var(--color-white)] dark:[--callout-background:color-mix(in_oklab,var(--color-zinc-400),transparent_90%)]',
                '[--callout-heading:var(--color-zinc-800)] dark:[--callout-heading:var(--color-zinc-200)]',
                '[--callout-text:var(--color-zinc-500)] dark:[--callout-text:var(--color-zinc-300)]',
                '[--callout-icon:var(--color-zinc-400)] dark:[--callout-icon:var(--color-zinc-400)]',
            ],
        })
        ;

    $iconWrapperClasses = Flux::classes()
        ->add('ps-2 py-2 pe-0 flex items-baseline')
        ;

    $iconClasses = Flux::classes()
        ->add('inline-block size-5 text-[var(--callout-icon)] dark:text-[var(--callout-icon)]')
        ->add($attributes->pluck('class:icon'))
        ;
?>

<div <?php echo e($attributes->class($classes)); ?> data-flux-callout>
    <?php if (is_string($icon) && $icon !== ''): ?>
        <div class="<?php echo e($iconWrapperClasses); ?>">
            <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => $icon,'variant' => $iconVariant,'class' => $iconClasses]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconVariant),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClasses)]); ?>
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
    <?php elseif ($icon): ?>
        <div <?php echo e($icon->attributes->class($iconWrapperClasses)); ?>>
            <?php echo e($icon); ?>

        </div>
    <?php endif; ?>

    <div class="ps-2 flex-1 <?php echo e($inline ? '@md:flex @md:[&>[data-slot="content"]:has([data-slot="heading"]):has([data-slot="text"])+[data-slot="actions"]]:p-2' : ''); ?>">
        <div class="flex-1 py-2 pe-3 @md:pe-4 flex flex-col justify-center gap-2" data-slot="content">
            <?php if ($heading): ?>
                <?php if (isset($component)) { $__componentOriginal6a8b23bb7915f0ff2da225b8cc3cc705 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6a8b23bb7915f0ff2da225b8cc3cc705 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::callout.heading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::callout.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e($heading); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6a8b23bb7915f0ff2da225b8cc3cc705)): ?>
<?php $attributes = $__attributesOriginal6a8b23bb7915f0ff2da225b8cc3cc705; ?>
<?php unset($__attributesOriginal6a8b23bb7915f0ff2da225b8cc3cc705); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6a8b23bb7915f0ff2da225b8cc3cc705)): ?>
<?php $component = $__componentOriginal6a8b23bb7915f0ff2da225b8cc3cc705; ?>
<?php unset($__componentOriginal6a8b23bb7915f0ff2da225b8cc3cc705); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if ($text): ?>
                <?php if (isset($component)) { $__componentOriginal16e285112e6431fda7b3d6f23c122381 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal16e285112e6431fda7b3d6f23c122381 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::callout.text','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::callout.text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e($text); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal16e285112e6431fda7b3d6f23c122381)): ?>
<?php $attributes = $__attributesOriginal16e285112e6431fda7b3d6f23c122381; ?>
<?php unset($__attributesOriginal16e285112e6431fda7b3d6f23c122381); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal16e285112e6431fda7b3d6f23c122381)): ?>
<?php $component = $__componentOriginal16e285112e6431fda7b3d6f23c122381; ?>
<?php unset($__componentOriginal16e285112e6431fda7b3d6f23c122381); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php echo e($content ?? $slot); ?>

        </div>

        <?php if ($actions): ?>
            <div <?php echo e($actions->attributes->class([
                $inline ? '@max-md:py-2 @md:m-[-2px] @md:ps-4 @md:justify-end @md:flex-row-reverse' : 'py-2',
                'self-start flex items-center gap-2'
            ])); ?> data-slot="actions">
                <?php echo e($actions); ?>

            </div>
        <?php endif; ?>
    </div>

    <?php if ($controls): ?>
        <div <?php echo e($controls->attributes->class($inline ? 'ps-2 m-[-2px]' : 'ps-2')); ?>>
            <?php echo e($controls); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\callout\index.blade.php ENDPATH**/ ?>