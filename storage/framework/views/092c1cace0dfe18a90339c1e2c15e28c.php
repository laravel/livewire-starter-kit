

<?php $iconVariant ??= $attributes->pluck('icon:variant'); ?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'iconVariant' => 'solid',
    'initials' => null,
    'tooltip' => null,
    'circle' => null,
    'color' => null,
    'badge' => null,
    'name' => null,
    'icon' => null,
    'size' => 'md',
    'src' => null,
    'href' => null,
    'alt' => null,
    'as' => 'div',
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
    'iconVariant' => 'solid',
    'initials' => null,
    'tooltip' => null,
    'circle' => null,
    'color' => null,
    'badge' => null,
    'name' => null,
    'icon' => null,
    'size' => 'md',
    'src' => null,
    'href' => null,
    'alt' => null,
    'as' => 'div',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
if ($name && ! $initials) {
    $parts = explode(' ', trim($name));

    if ($attributes->pluck('initials:single')) {
        $initials = strtoupper(mb_substr($parts[0], 0, 1));
    } else {
        // Remove empty strings from the array...
        $parts = collect($parts)->filter()->values()->all();

        if (count($parts) > 1) {
            $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
        } else if (count($parts) === 1) {
            $initials = strtoupper(mb_substr($parts[0], 0, 1)) . strtolower(mb_substr($parts[0], 1, 1));
        }
    }
}

if ($name && $tooltip === true) {
    $tooltip = $name;
}

$hasTextContent = $icon ?? $initials ?? $slot->isNotEmpty();

// If there's no text content, we'll fallback to using the user icon otherwise there will be an empty white square...
if (! $hasTextContent) {
    $icon = 'user';
    $hasTextContent = true;
}

// Be careful not to change the order of these colors.
// They're used in the hash function below and changing them would change actual user avatar colors that they might have grown to identify with.
$colors = ['red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose'];

if ($hasTextContent && $color === 'auto') {
    $colorSeed = $attributes->pluck('color:seed') ?? $name ?? $icon ?? $initials ?? $slot;
    $hash = crc32((string) $colorSeed);
    $color = $colors[$hash % count($colors)];
}

$classes = Flux::classes()
    ->add(match($size) {
        'xl' => '[:where(&)]:size-16 [:where(&)]:text-base',
        'lg' => '[:where(&)]:size-12 [:where(&)]:text-base',
        default => '[:where(&)]:size-10 [:where(&)]:text-sm',
        'sm' => '[:where(&)]:size-8 [:where(&)]:text-sm',
        'xs' => '[:where(&)]:size-6 [:where(&)]:text-xs',
    })
    ->add($circle ? '[--avatar-radius:calc(infinity*1px)]' : match($size) {
        'xl' => '[--avatar-radius:var(--radius-xl)]',
        'lg' => '[--avatar-radius:var(--radius-lg)]',
        default => '[--avatar-radius:var(--radius-lg)]',
        'sm' => '[--avatar-radius:var(--radius-md)]',
        'xs' => '[--avatar-radius:var(--radius-sm)]',
    })
    ->add('relative flex-none isolate flex items-center justify-center')
    ->add('[:where(&)]:font-medium')
    ->add('rounded-[var(--avatar-radius)]')
    ->add($hasTextContent ? '[:where(&)]:bg-zinc-200 [:where(&)]:dark:bg-zinc-600 [:where(&)]:text-zinc-800 [:where(&)]:dark:text-white' : '')
    ->add(match($color) {
        'red' => 'bg-red-200 text-red-800',
        'orange' => 'bg-orange-200 text-orange-800',
        'amber' => 'bg-amber-200 text-amber-800',
        'yellow' => 'bg-yellow-200 text-yellow-800',
        'lime' => 'bg-lime-200 text-lime-800',
        'green' => 'bg-green-200 text-green-800',
        'emerald' => 'bg-emerald-200 text-emerald-800',
        'teal' => 'bg-teal-200 text-teal-800',
        'cyan' => 'bg-cyan-200 text-cyan-800',
        'sky' => 'bg-sky-200 text-sky-800',
        'blue' => 'bg-blue-200 text-blue-800',
        'indigo' => 'bg-indigo-200 text-indigo-800',
        'violet' => 'bg-violet-200 text-violet-800',
        'purple' => 'bg-purple-200 text-purple-800',
        'fuchsia' => 'bg-fuchsia-200 text-fuchsia-800',
        'pink' => 'bg-pink-200 text-pink-800',
        'rose' => 'bg-rose-200 text-rose-800',
        default => '',
    })
    ->add(true ? [
        'after:absolute after:inset-0 after:inset-ring-[1px] after:inset-ring-black/7 dark:after:inset-ring-white/10',
        $circle ? 'after:rounded-full' : match($size) {
            'xl' => 'after:rounded-xl',
            'lg' => 'after:rounded-lg',
            default => 'after:rounded-lg',
            'sm' => 'after:rounded-md',
            'xs' => 'after:rounded-sm',
        },
    ] : []);

$iconClasses = Flux::classes()
    ->add('opacity-75')
    ->add(match($size) {
        'lg' => 'size-8',
        default => 'size-6',
        'sm' => 'size-5',
        'xs' => 'size-4',
    });

$badgeColor = $attributes->pluck('badge:color') ?: (is_object($badge) ? $badge?->attributes?->pluck('color') : null);
$badgeCircle = $attributes->pluck('badge:circle') ?: (is_object($badge) ? $badge?->attributes?->pluck('circle') : null);
$badgePosition = $attributes->pluck('badge:position') ?: (is_object($badge) ? $badge?->attributes?->pluck('position') : null);
$badgeVariant = $attributes->pluck('badge:variant') ?: (is_object($badge) ? $badge?->attributes?->pluck('variant') : null);

$badgeClasses = Flux::classes()
    ->add('absolute ring-[2px] ring-white dark:ring-zinc-900 z-10')
    ->add(match($size) {
        default => 'h-3 min-w-3',
        'sm' => 'h-2 min-w-2',
        'xs' => 'h-2 min-w-2',
    })
    ->add('flex items-center justify-center tabular-nums overflow-hidden')
    ->add('text-[.625rem] text-zinc-800 dark:text-white font-medium')
    ->add($badgeCircle ? 'rounded-full' : 'rounded-[3px]')
    ->add($badgeVariant === 'outline' ? [
        'after:absolute after:inset-[3px] after:bg-white dark:after:bg-zinc-900',
        $badgeCircle ? 'after:rounded-full' : 'after:rounded-[1px]',
    ] : [])
    ->add(match($badgePosition) {
        'top left' => 'top-0 left-0',
        'top right' => 'top-0 right-0',
        'bottom left' => 'bottom-0 left-0',
        'bottom right' => 'bottom-0 right-0',
        default => 'bottom-0 right-0',
    })
    ->add(match($badgeColor) {
        'red' => 'bg-red-500 dark:bg-red-400',
        'orange' => 'bg-orange-500 dark:bg-orange-400',
        'amber' => 'bg-amber-500 dark:bg-amber-400',
        'yellow' => 'bg-yellow-500 dark:bg-yellow-400',
        'lime' => 'bg-lime-500 dark:bg-lime-400',
        'green' => 'bg-green-500 dark:bg-green-400',
        'emerald' => 'bg-emerald-500 dark:bg-emerald-400',
        'teal' => 'bg-teal-500 dark:bg-teal-400',
        'cyan' => 'bg-cyan-500 dark:bg-cyan-400',
        'sky' => 'bg-sky-500 dark:bg-sky-400',
        'blue' => 'bg-blue-500 dark:bg-blue-400',
        'indigo' => 'bg-indigo-500 dark:bg-indigo-400',
        'violet' => 'bg-violet-500 dark:bg-violet-400',
        'purple' => 'bg-purple-500 dark:bg-purple-400',
        'fuchsia' => 'bg-fuchsia-500 dark:bg-fuchsia-400',
        'pink' => 'bg-pink-500 dark:bg-pink-400',
        'rose' => 'bg-rose-500 dark:bg-rose-400',
        'zinc' => 'bg-zinc-400 dark:bg-zinc-300',
        'gray' => 'bg-zinc-400 dark:bg-zinc-300',
        default => 'bg-white dark:bg-zinc-900',
    })
    ;

$label = $alt ?? $name;
?>

<?php if (isset($component)) { $__componentOriginal1bac653003a70249c8d1bced240ca490 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1bac653003a70249c8d1bced240ca490 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::with-tooltip','data' => ['tooltip' => $tooltip,'attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::with-tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tooltip' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tooltip),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
    <?php if (isset($component)) { $__componentOriginal41290c80ee95fab383f81660ba8bf860 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41290c80ee95fab383f81660ba8bf860 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button-or-link','data' => ['attributes' => $attributes->class($classes)->merge($circle ? ['data-circle' => 'true'] : []),'as' => $as,'href' => $href,'dataFluxAvatar' => true,'dataSlot' => 'avatar','dataSize' => ''.e($size).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button-or-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->class($classes)->merge($circle ? ['data-circle' => 'true'] : [])),'as' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($as),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($href),'data-flux-avatar' => true,'data-slot' => 'avatar','data-size' => ''.e($size).'']); ?>
        <?php if ($src): ?>
            <img src="<?php echo e($src); ?>" alt="<?php echo e($alt ?? $name); ?>" class="rounded-[var(--avatar-radius)] size-full object-cover">
        <?php elseif ($icon): ?>
            <?php if (isset($component)) { $__componentOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7d5f44bf2a2d803ed0b55f72f1f82e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.index','data' => ['name' => $icon,'variant' => $iconVariant,'class' => $iconClasses]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconVariant),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClasses)]); ?>
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
        <?php elseif ($hasTextContent): ?>
            <span class="select-none"><?php echo e($initials ?? $slot); ?></span>
        <?php endif; ?>

        <?php if ($badge instanceof \Illuminate\View\ComponentSlot): ?>
            <div <?php echo e($badge->attributes->class($badgeClasses)); ?> aria-hidden="true"><?php echo e($badge); ?></div>
        <?php elseif ($badge): ?>
            <div class="<?php echo e($badgeClasses); ?>" aria-hidden="true"><?php echo e(is_string($badge) ? $badge : ''); ?></div>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal41290c80ee95fab383f81660ba8bf860)): ?>
<?php $attributes = $__attributesOriginal41290c80ee95fab383f81660ba8bf860; ?>
<?php unset($__attributesOriginal41290c80ee95fab383f81660ba8bf860); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal41290c80ee95fab383f81660ba8bf860)): ?>
<?php $component = $__componentOriginal41290c80ee95fab383f81660ba8bf860; ?>
<?php unset($__componentOriginal41290c80ee95fab383f81660ba8bf860); ?>
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
<?php /**PATH D:\xampp\htdocs\Laravel\Flexcon-tracker\vendor\livewire\flux\src/../stubs/resources/views/flux/avatar/index.blade.php ENDPATH**/ ?>