

<?php $iconTrailing ??= $attributes->pluck('icon:trailing'); ?>
<?php $iconLeading ??= $attributes->pluck('icon:leading'); ?>
<?php $iconVariant ??= $attributes->pluck('icon:variant'); ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'iconTrailing' => null,
    'variant' => 'outline',
    'iconVariant' => null,
    'iconLeading' => null,
    'type' => 'button',
    'loading' => null,
    'align' => 'center',
    'size' => 'base',
    'square' => null,
    'color' => null,
    'inset' => null,
    'icon' => null,
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
    'iconTrailing' => null,
    'variant' => 'outline',
    'iconVariant' => null,
    'iconLeading' => null,
    'type' => 'button',
    'loading' => null,
    'align' => 'center',
    'size' => 'base',
    'square' => null,
    'color' => null,
    'inset' => null,
    'icon' => null,
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
$iconLeading = $icon ??= $iconLeading;

// Button should be a square if it has no text contents...
$square ??= $slot->isEmpty();

// Size-up icons in square/icon-only buttons... (xs buttons just get micro size/style...)
$iconVariant ??= ($size === 'xs')
    ? ($square ? 'micro' : 'micro')
    : ($square ? 'mini' : 'micro');

$iconTrailingVariant ??= $attributes->pluck('icon-trailing:variant', $iconVariant);

// When using the outline icon variant, we need to size it down to match the default icon sizes...
$iconClasses = Flux::classes()
    ->add($iconVariant === 'outline' ? ($square && $size !== 'xs' ? 'size-5' : 'size-4') : '')
    ->add($attributes->pluck('icon:class'))
    ;

$iconTrailingClasses = Flux::classes()
    ->add($iconTrailingVariant === 'outline' ? ($square && $size !== 'xs' ? 'size-5' : 'size-4') : '')
    ->add($attributes->pluck('icon-trailing:class'))
    ;

$isTypeSubmitAndNotDisabledOnRender = $type === 'submit' && ! $attributes->has('disabled');

$isJsMethod = str_starts_with($attributes->whereStartsWith('wire:click')->first() ?? '', '$js.');

$loading ??= $loading ?? ($isTypeSubmitAndNotDisabledOnRender || $attributes->whereStartsWith('wire:click')->isNotEmpty() && ! $isJsMethod);

if ($loading && $type !== 'submit' && ! $isJsMethod) {
    $attributes = $attributes->merge(['wire:loading.attr' => 'data-flux-loading']);

    // We need to add `wire:target` here because without it the loading indicator won't be scoped
    // by method params, causing multiple buttons with the same method but different params to
    // trigger each other's loading indicators...
    if (! $attributes->has('wire:target') && $target = $attributes->whereStartsWith('wire:click')->first()) {
        $attributes = $attributes->merge(['wire:target' => $target], escape: false);
    }
}

$classes = Flux::classes()
    ->add('relative items-center font-medium justify-center gap-2 whitespace-nowrap')
    ->add('disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none')
    ->add(match ($align) {
        'start' => 'justify-start',
        'center' => 'justify-center',
        'end' => 'justify-end',
    })
    ->add(match ($size) { // Size...
        'base' => 'h-10 text-sm rounded-lg' . ' ' . (
            $square
                ? 'w-10'
                // If we have an icon, we want to reduce the padding on the side that has the icon...
                : ($iconLeading && $iconLeading !== '' ? 'ps-3' : 'ps-4') . ' ' . ($iconTrailing && $iconTrailing !== '' ? 'pe-3' : 'pe-4')
        ),
        'sm' => 'h-8 text-sm rounded-md' . ' ' . ($square ? 'w-8' : 'px-3'),
        'xs' => 'h-6 text-xs rounded-md' . ' ' . ($square ? 'w-6' : 'px-2'),
    })
    ->add('inline-flex') // Buttons are inline by default but links are blocks, so inline-flex is needed here to ensure link-buttons are displayed the same as buttons...
    ->add($inset ? match ($size) { // Inset...
        'base' => $square
            ? Flux::applyInset($inset, top: '-mt-2.5', right: '-me-2.5', bottom: '-mb-2.5', left: '-ms-2.5')
            : Flux::applyInset($inset, top: '-mt-2.5', right: '-me-4', bottom: '-mb-3', left: '-ms-4'),
        'sm' => $square
            ? Flux::applyInset($inset, top: '-mt-1.5', right: '-me-1.5', bottom: '-mb-1.5', left: '-ms-1.5')
            : Flux::applyInset($inset, top: '-mt-1.5', right: '-me-3', bottom: '-mb-1.5', left: '-ms-3'),
        'xs' => $square
            ? Flux::applyInset($inset, top: '-mt-1', right: '-me-1', bottom: '-mb-1', left: '-ms-1')
            : Flux::applyInset($inset, top: '-mt-1', right: '-me-2', bottom: '-mb-1', left: '-ms-2'),
    } : '')
    ->add(match ($variant) { // Background color...
        'primary' => 'bg-[var(--color-accent)] hover:bg-[color-mix(in_oklab,_var(--color-accent),_transparent_10%)]',
        'filled' => 'bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20',
        'outline' => 'bg-white hover:bg-zinc-50 dark:bg-zinc-700 dark:hover:bg-zinc-600/75',
        'danger' => 'bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500',
        'ghost' => 'bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15',
        'subtle' => 'bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15',
    })
    ->add(match ($variant) { // Text color...
        'primary' => 'text-[var(--color-accent-foreground)]',
        'filled' => 'text-zinc-800 dark:text-white',
        'outline' => 'text-zinc-800 dark:text-white',
        'danger' => 'text-white',
        'ghost' => 'text-zinc-800 dark:text-white',
        'subtle' => 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white',
    })
    ->add(match ($variant) { // Border color...
        'primary' => 'border border-black/10 dark:border-0',
        'outline' => 'border border-zinc-200 hover:border-zinc-200 border-b-zinc-300/80 dark:border-zinc-600 dark:hover:border-zinc-600',
         default => '',
    })
    ->add(match ($variant) { // Shadows...
        'primary' => 'shadow-[inset_0px_1px_--theme(--color-white/.2)]',
        'danger' => 'shadow-[inset_0px_1px_var(--color-red-500),inset_0px_2px_--theme(--color-white/.15)] dark:shadow-none',
        'outline' => match ($size) {
            'base' => 'shadow-xs',
            'sm' => 'shadow-xs',
            'xs' => 'shadow-none',
        },
        default => '',
    })
    ->add(match ($variant) { // Grouped border treatments...
        'ghost' => '',
        'subtle' => '',
        'outline' => '[[data-flux-button-group]_&]:border-s-0 [:is([data-flux-button-group]>&:first-child,_[data-flux-button-group]_:first-child>&)]:border-s-[1px]',
        'filled' => '[[data-flux-button-group]_&]:border-e [:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-0 [[data-flux-button-group]_&]:border-zinc-200/80 dark:[[data-flux-button-group]_&]:border-zinc-900/50',
        'danger' => '[[data-flux-button-group]_&]:border-e [:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-0 [[data-flux-button-group]_&]:border-red-600 dark:[[data-flux-button-group]_&]:border-red-900/25',
        'primary' => '[[data-flux-button-group]_&]:border-e-0 [:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-[1px] dark:[:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-0 dark:[:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-s-[1px] [:is([data-flux-button-group]>&:not(:first-child),_[data-flux-button-group]_:not(:first-child)>&)]:border-s-[color-mix(in_srgb,var(--color-accent-foreground),transparent_85%)]',
    })
    ->add($loading ? [ // Loading states...
        '*:transition-opacity',
        $type === 'submit' ? '[&[disabled]>:not([data-flux-loading-indicator])]:opacity-0' : '[&[data-loading]>:not([data-flux-loading-indicator])]:opacity-0 [&[data-flux-loading]>:not([data-flux-loading-indicator])]:opacity-0',
        $type === 'submit' ? '[&[disabled]>[data-flux-loading-indicator]]:opacity-100' : '[&[data-loading]>[data-flux-loading-indicator]]:opacity-100 [&[data-flux-loading]>[data-flux-loading-indicator]]:opacity-100',
        $type === 'submit' ? '[&[disabled]]:pointer-events-none' : 'data-loading:pointer-events-none data-flux-loading:pointer-events-none',
    ] : [])
    ->add($variant === 'primary' ? match ($color) {
        'slate' => '[--color-accent:var(--color-slate-800)] [--color-accent-content:var(--color-slate-800)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-white)] dark:[--color-accent-content:var(--color-white)] dark:[--color-accent-foreground:var(--color-slate-800)]',
        'gray' => '[--color-accent:var(--color-gray-800)] [--color-accent-content:var(--color-gray-800)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-white)] dark:[--color-accent-content:var(--color-white)] dark:[--color-accent-foreground:var(--color-gray-800)]',
        'zinc' => '[--color-accent:var(--color-zinc-800)] [--color-accent-content:var(--color-zinc-800)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-white)] dark:[--color-accent-content:var(--color-white)] dark:[--color-accent-foreground:var(--color-zinc-800)]',
        'neutral' => '[--color-accent:var(--color-neutral-800)] [--color-accent-content:var(--color-neutral-800)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-white)] dark:[--color-accent-content:var(--color-white)] dark:[--color-accent-foreground:var(--color-neutral-800)]',
        'stone' => '[--color-accent:var(--color-stone-800)] [--color-accent-content:var(--color-stone-800)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-white)] dark:[--color-accent-content:var(--color-white)] dark:[--color-accent-foreground:var(--color-stone-800)]',
        'red' => '[--color-accent:var(--color-red-500)] [--color-accent-content:var(--color-red-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-red-500)] dark:[--color-accent-content:var(--color-red-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'orange' => '[--color-accent:var(--color-orange-500)] [--color-accent-content:var(--color-orange-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-orange-400)] dark:[--color-accent-content:var(--color-orange-400)] dark:[--color-accent-foreground:var(--color-orange-950)]',
        'amber' => '[--color-accent:var(--color-amber-400)] [--color-accent-content:var(--color-amber-600)] [--color-accent-foreground:var(--color-amber-950)] dark:[--color-accent:var(--color-amber-400)] dark:[--color-accent-content:var(--color-amber-400)] dark:[--color-accent-foreground:var(--color-amber-950)]',
        'yellow' => '[--color-accent:var(--color-yellow-400)] [--color-accent-content:var(--color-yellow-600)] [--color-accent-foreground:var(--color-yellow-950)] dark:[--color-accent:var(--color-yellow-400)] dark:[--color-accent-content:var(--color-yellow-400)] dark:[--color-accent-foreground:var(--color-yellow-950)]',
        'lime' => '[--color-accent:var(--color-lime-400)] [--color-accent-content:var(--color-lime-600)] [--color-accent-foreground:var(--color-lime-900)] dark:[--color-accent:var(--color-lime-400)] dark:[--color-accent-content:var(--color-lime-400)] dark:[--color-accent-foreground:var(--color-lime-950)]',
        'green' => '[--color-accent:var(--color-green-600)] [--color-accent-content:var(--color-green-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-green-600)] dark:[--color-accent-content:var(--color-green-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'emerald' => '[--color-accent:var(--color-emerald-600)] [--color-accent-content:var(--color-emerald-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-emerald-600)] dark:[--color-accent-content:var(--color-emerald-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'teal' => '[--color-accent:var(--color-teal-600)] [--color-accent-content:var(--color-teal-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-teal-600)] dark:[--color-accent-content:var(--color-teal-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'cyan' => '[--color-accent:var(--color-cyan-600)] [--color-accent-content:var(--color-cyan-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-cyan-600)] dark:[--color-accent-content:var(--color-cyan-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'sky' => '[--color-accent:var(--color-sky-600)] [--color-accent-content:var(--color-sky-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-sky-600)] dark:[--color-accent-content:var(--color-sky-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'blue' => '[--color-accent:var(--color-blue-500)] [--color-accent-content:var(--color-blue-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-blue-500)] dark:[--color-accent-content:var(--color-blue-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'indigo' => '[--color-accent:var(--color-indigo-500)] [--color-accent-content:var(--color-indigo-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-indigo-500)] dark:[--color-accent-content:var(--color-indigo-300)] dark:[--color-accent-foreground:var(--color-white)]',
        'violet' => '[--color-accent:var(--color-violet-500)] [--color-accent-content:var(--color-violet-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-violet-500)] dark:[--color-accent-content:var(--color-violet-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'purple' => '[--color-accent:var(--color-purple-500)] [--color-accent-content:var(--color-purple-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-purple-500)] dark:[--color-accent-content:var(--color-purple-300)] dark:[--color-accent-foreground:var(--color-white)]',
        'fuchsia' => '[--color-accent:var(--color-fuchsia-600)] [--color-accent-content:var(--color-fuchsia-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-fuchsia-600)] dark:[--color-accent-content:var(--color-fuchsia-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'pink' => '[--color-accent:var(--color-pink-600)] [--color-accent-content:var(--color-pink-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-pink-600)] dark:[--color-accent-content:var(--color-pink-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'rose' => '[--color-accent:var(--color-rose-500)] [--color-accent-content:var(--color-rose-500)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-rose-500)] dark:[--color-accent-content:var(--color-rose-400)] dark:[--color-accent-foreground:var(--color-white)]',
        default => '',
    } : '')
    ;

    // Exempt subtle and ghost buttons from receiving border roundness overrides from button.group...
    $attributes = $attributes->merge([
        'data-flux-group-target' => ! in_array($variant, ['subtle', 'ghost']),
    ]);
?>

<?php if (isset($component)) { $__componentOriginal1bac653003a70249c8d1bced240ca490 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1bac653003a70249c8d1bced240ca490 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::with-tooltip','data' => ['attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::with-tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
    <?php if (isset($component)) { $__componentOriginal44af18836315d1ca840cbdae84566f63 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal44af18836315d1ca840cbdae84566f63 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button-or-link-pure','data' => ['type' => $type,'attributes' => $attributes->class($classes),'dataFluxButton' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button-or-link-pure'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($type),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->class($classes)),'data-flux-button' => true]); ?>
        <?php if ($loading): ?>
            <div class="absolute inset-0 flex items-center justify-center opacity-0" data-flux-loading-indicator>
                <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => 'loading','variant' => $iconVariant,'class' => $iconClasses]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'loading','variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconVariant),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClasses)]); ?>
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
        <?php endif; ?>

        <?php if (is_string($iconLeading) && $iconLeading !== ''): ?>
            <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => $iconLeading,'variant' => $iconVariant,'class' => $iconClasses]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconLeading),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconVariant),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClasses)]); ?>
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
        <?php elseif ($iconLeading): ?>
            <?php echo e($iconLeading); ?>

        <?php endif; ?>

        <?php if (($loading || $iconLeading || $iconTrailing) && ! $slot->isEmpty()): ?>
            
            
            <span><?php echo e($slot); ?></span>
        <?php else: ?>
            <?php echo e($slot); ?>

        <?php endif; ?>

        <?php if ($kbd): ?>
            <div class="text-xs text-zinc-400 dark:text-zinc-400"><?php echo e($kbd); ?></div>
        <?php endif; ?>

        <?php if (is_string($iconTrailing) && $iconTrailing !== ''): ?>
            
            <?php $iconClasses->add($square ? '' : '-ms-1'); ?>
            <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['icon' => $iconTrailing,'variant' => $iconTrailingVariant,'class' => $iconTrailingClasses]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconTrailing),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconTrailingVariant),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconTrailingClasses)]); ?>
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
        <?php elseif ($iconTrailing): ?>
            <?php echo e($iconTrailing); ?>

        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal44af18836315d1ca840cbdae84566f63)): ?>
<?php $attributes = $__attributesOriginal44af18836315d1ca840cbdae84566f63; ?>
<?php unset($__attributesOriginal44af18836315d1ca840cbdae84566f63); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal44af18836315d1ca840cbdae84566f63)): ?>
<?php $component = $__componentOriginal44af18836315d1ca840cbdae84566f63; ?>
<?php unset($__componentOriginal44af18836315d1ca840cbdae84566f63); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1bac653003a70249c8d1bced240ca490)): ?>
<?php $attributes = $__attributesOriginal1bac653003a70249c8d1bced240ca490; ?>
<?php unset($__attributesOriginal1bac653003a70249c8d1bced240ca490); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1bac653003a70249c8d1bced240ca490)): ?>
<?php $component = $__componentOriginal1bac653003a70249c8d1bced240ca490; ?>
<?php unset($__componentOriginal1bac653003a70249c8d1bced240ca490); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\button\index.blade.php ENDPATH**/ ?>