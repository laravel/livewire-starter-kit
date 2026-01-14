<?php
extract(Flux::forwardedAttributes($attributes, [
    'type',
    'current',
    'href',
    'as',
]));
?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'button',
    'current' => null,
    'href' => null,
    'as' => null,
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
    'type' => 'button',
    'current' => null,
    'href' => null,
    'as' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php

$hrefForCurrentDetection = str($href)->startsWith(trim(config('app.url')))
    ? (string) str($href)->after(trim(config('app.url'), '/'))
    : $href;

if ($hrefForCurrentDetection === '') $hrefForCurrentDetection = '/';

$requestIs = function ($pattern) {
    // Support current route detection during Livewire update requests as well...
    return app('livewire')?->isLivewireRequest()
        ? str()->is($pattern, app('livewire')->originalPath())
        : request()->is($pattern);
};

$current = $current === null ? ($hrefForCurrentDetection
    ? $requestIs($hrefForCurrentDetection === '/' ? '/' : trim($hrefForCurrentDetection, '/'))
    : false) : $current;
?>

<?php if ($as === 'div' && ! $href): ?>
    <div <?php echo e($attributes); ?>>
        <?php echo e($slot); ?>

    </div>
<?php elseif ($as === 'a' || $href): ?>
    
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['data-current' => $current])); ?>>
        <?php echo e($slot); ?>

    </a>
<?php else: ?>
    <button <?php echo e($attributes->merge(['type' => $type, 'data-current' => $current])); ?>>
        <?php echo e($slot); ?>

    </button>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\flexcon-tracker\vendor\livewire\flux\stubs\resources\views\flux\button-or-link.blade.php ENDPATH**/ ?>